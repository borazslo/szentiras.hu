<?php

class Menu {

    var $items;  // Items in our shopping cart

    function add_item($title, $url) {
        $this->items[] = array('url'=>$url,'title'=>$title);
    }

	function add_pause() {
        $this->items[] = 'pause';
    }
	
	function add_text($text) {
        $this->items[] = $text;
    }
	
    // Take $num articles of $artnr out of the cart

    function remove_item($artnr, $num) {
        if ($this->items[$artnr] > $num) {
            $this->items[$artnr] -= $num;
            return true;
        } elseif ($this->items[$artnr] == $num) {
            unset($this->items[$artnr]);
            return true;
        } else {
            return false;
        }
    }
	
	function html() {
		echo"
		<table border='0' cellpadding='0' cellspacing='0' width='750'>";
		
		foreach($this->items as $item) {	
		if($item=='pause') {
			echo"<tr><td style='height:15px'></tr>";
		}
		elseif(!is_array($item))  {
			echo"<tr><td style='background-color:#9DA7D8;padding:5px;padding-left:40px'>".$item."</tr>";
		}
		else {
		echo"<tr>
            <td style='height:3px'></tr>
			<tr>
            <td background='../img/vmenucolorbg.gif' width='140' align='left' style='background-color:#9DA7D8;padding:5px;padding-left:40px'>";		  
					  
		echo '<a href="'.url($item['url']).'" class="menulink">'.$item['title'].'</a>';
        
		echo"
                  </td>
                </tr>";
			}
		}
		echo "</table>";
	
	}
}

function url($url) {
	global $baseurl;
	if(!preg_match('/(http:\/\/|^\/)/i',$url)) {
		$tmp = explode('?',$url);
		if(isset($tmp[1])) {
			$vars = explode('&',$tmp[1]);
			foreach($vars as $key => $var) {
				if(preg_match('/^q=/i',$var)) unset($vars[$key]);
			}
			$tmp[1] = implode('&',$vars);
		}
		$newurl = $baseurl.'index.php?q='.$tmp[0];
		if(isset($tmp[1])) $newurl .= '&'.$tmp[1];
	} else $newurl = $url;

	return $newurl; 
}
?>