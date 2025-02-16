<?php

namespace SzentirasHu\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Storage;
use SzentirasHu\Models\Media;
use SzentirasHu\Models\MediaType;

class UploadMediaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'szentiras:media
        {action=: The action to execute. Possible values: create, delete}
        {--file= : If creatint, the path to the file to upload. The file name must be prepared: USX_Chapter_Verse.jpg. If deleting, the id.}
        {--type= : If creating a file or deleting a type, only the type name. If creating a type, the type specification, in the format: "name -- website -- license.". If a type with this name exists, it will be updated, otherwise a new type will be created. If website and license is not given, the name will be used to associate the image.};

    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload media and assign to books, chapters, verses.
    
    First you need to create the type like 
    php artisan szentiras:media create --type="SweetPublishing -- http://sweetpublishing.com -- CC BY-SA 3.0"

    Then you can upload the file like
    php artisan szentiras:media create --file=/path/to/file.jpg --type="SweetPublishing"
    ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->argument('action') == 'create') {
            if ($this->option("file")) {
                // lookup the content of type option by name
                if (!$this->option("type")) {
                    $this->error("Type --type option is required when uploading a file.");
                    return;
                }
                $mediaType = MediaType::where('name', $this->option("type"))->first();
                if (!$mediaType) {
                    $this->error("Media type not found: " . $this->option("type"));
                    return;
                }
                $path = $this->option("file");
                if (!file_exists($path)) {
                    $this->error("File not found: $path");
                    return;
                }
                $filename=basename($path);
                // cut the extension
                $filename = substr($filename, 0, strrpos($filename, "."));
                // parse the filename to USX_Chapter_Verse
                $parts = explode("_", $filename);
                if (count($parts) != 3) {
                    $this->error("Filename must be in the format: USX_Chapter_Verse.jpg");
                    return;
                }
                $usx = $parts[0];
                $chapter = $parts[1];
                $verse = $parts[2];
                // check if for the given type, usx, chapter, verse there is already a media
                $existing = Media::where('media_type_id', $mediaType->id)
                    ->where('usx_code', $usx)
                    ->where('chapter', $chapter)
                    ->where('verse', $verse)
                    ->first();
                if ($existing) {                
                    // update the file
                    $this->info("Media already exists: $path. Id: $existing->id. Updating.");
                    Storage::delete("media/{$existing->id}");
                    $existing->delete();                    
                }
                $mimeType = mime_content_type($path);
                $media = $mediaType->media()->create([
                    'uuid' => Str::uuid(),
                    'filename' => $filename,
                    'mime_type' => $mimeType,
                    'usx_code' => $usx,
                    'chapter' => $chapter,
                    'verse' => $verse,
                ]);
                $file = file_get_contents($path);
                Storage::put("media/{$media->id}", $file);
                $this->info("Media uploaded: $path. Id: $media->id");
            } else {
                if ($this->option("type")) {
                    // the input must be in the format: "name -- website -- license"
                    $type = explode(" -- ", $this->option("type"));
                    $name = $type[0];
                    $website = $type[1] ?? null;
                    $license = $type[2] ?? null;
                    $mediaType = MediaType::updateOrCreate(['name' => $name], ['website' => $website, 'license' => $license]);
                    $this->info("Media type created: $mediaType->name. Id: $mediaType->id");
                }
            }
        } else if ($this->argument('action') == 'delete') {
            if ($this->option("type")) {
                $name = $this->option("type");
                $mediaType = MediaType::where('name', $name)->first();
                if ($mediaType) {
                    $mediaType->delete();
                    $this->info("Media type deleted: $mediaType->name");
                } else {
                    $this->error("Media type not found: $name");
                }
            } else if ($this->option("file")) {
                $path = $this->option("file");
                $media = Media::where('id', $path)->first();
                if ($media) {
                    $media->delete();
                    Storage::delete("media/{$media->id}");
                    $this->info("Media deleted: $path");
                } else {
                    $this->error("Media not found: $path");
                }
            }
        } else {
            $this->error("Invalid action: " . $this->argument('action'));
        }
    }
}
