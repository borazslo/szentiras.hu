-- rövidítéses tábla
CREATE TABLE kar_tdbook_hibas (
	`id` tinyint unsigned not null,
	`abbrev` nvarchar(100) not null,

	PRIMARY KEY (`abbrev`)
);