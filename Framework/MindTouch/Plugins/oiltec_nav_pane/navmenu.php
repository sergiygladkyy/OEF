<?php
/*
 * navmenu.php - this file is a part of the Oiltec Solutions web skin
 * It contains implementation of the NavMenuItem and NavMenuclasses which 
 * are designed to parse navigation menu XML files
 * (c) Oiltec Solutions AS, 2011
 */

class NavMenuItem
{
	public $title = "";
	public $url = "";
	public $level = 0;

	public $menuItems = NULL;
	
	public function __construct($title = NULL, $url = NULL, $level = 0)
	{
		if ($title != NULL)
		{
			$this->title = $title;
			$this->url = $url;
		}
		$this->level = $level;
		$this->menuItems = array();
	}
	
	public function Clear()
	{
		$this->title = "";
		$this->url = "";
		$this->level = "";
		if ($this->menuItems != NULL)
		{
			foreach($this->menuItems as $item)
				$item->Clear();
			unset($this->menuItems);
			$this->menuItems = NULL;
		}
	}
	
	public function AddChildren(NavMenuItem $child)
	{
		$child->level = $this->level + 1;
		$this->menuItems[count($this->menuItems)] = $child;
	}
}

// class NavMenu stores 2 level menu items
class NavMenu
{
	public $menuItems = NULL;
	
	public function __construct($fileName = NULL)
	{
		if ($fileName != NULL) $this->ParseXML($fileName);
	}
	
	// Clears items, reset class
	public function Clear()
	{
		if ($this->menuItems != NULL)
		{
			foreach($this->menuItems as $item)
				$item->Clear();
			unset($this->menuItems);
			$this->menuItems = NULL;
		}
	}
	
	// Parses XML file
	public function ParseXML($fileName)
	{
		$this->Clear();
		
		if (!is_file($fileName))
			return;
		
		$xmlStr = file_get_contents($fileName);
		
		$parser = xml_parser_create();
    	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $xmlStr, $xmlVals, $xmlIndex);
		xml_parser_free($parser);
	
		$this->XmlToItems($xmlVals, $xmlIndex);
	}
	
	private function XmlToItems($xmlVals, $xmlIndex)
	{
		$last_index = 0;
    	foreach ($xmlVals as $value) 
    	{
    		if ($value["level"] < 2)
    			continue;
    		if ($value["type"] == "close")
    			continue;
    			
    		if ($value["level"] == 2)
    		{
     			$item = new NavMenuItem($value["attributes"]["title"],
    				$value["attributes"]["url"]);
    			$this->menuItems[$last_index++] = $item;
    		}
    		else 
    		{
    			if ($last_index - 1 >= 0)
    			{
	     			$item = new NavMenuItem($value["attributes"]["title"],
	    				$value["attributes"]["url"]);
    				$this->menuItems[$last_index - 1]->AddChildren($item);
    			}
       		}
        }
	}
	
}

?>