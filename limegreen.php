<?php

define('LIMEGREEN_NAMESPACE', 'http://code.google.com/p/limegreen');

class Limegreen {
	function __construct ($filename) {
		$this->xml = new SimpleXMLElement(file_get_contents($filename));
		$this->entries = $this->xml->entry;
	}
	function compare($build1, $build2, $comparebuild = true) {
		$version1 = split('\.',$build1->version);
		$version2 = split('\.',$build2->version);
		$build1 = $build1->build;
		$build2 = $build2->build;
		$swap = false;
		if (count($version2) > count($version1)) {
			$compare1 = $version2;
			$compare2 = $version1;
			$comparebuild1 = $build2;
			$comparebuild2 = $build1;
			$swap = true;
		} else {
			$compare1 = $version1;
			$compare2 = $version2;
			$comparebuild1 = $build1;
			$comparebuild2 = $build2;
		}
		for($i = 0; $i<count($compare1); $i++){
			if (i<count($compare2)) {
				$part1 = $compare1[$i];
				$part2 = $compare2[$i];
				if ($part1 > $part2) {
					return ($swap ? 1 : -1);
				} else if ($part2 > $part1) {
					return ($swap ? -1 : 1);
				}
			} else {
				return ($swap ? 1 : -1);
			}
		}
		if ($comparebuild) { 
			if ($comparebuild1 > $comparebuild2) {
				return  ($swap ? 1 : -1);
			} else if ($comparebuild1 < $comparebuild2) {
				return  ($swap ? -1 : 1);
			}
		}
		return 0;
	}
	function getLatest($build1, $build2) {
		if ($this->compare($build1, $build2) <= 0) {
			return $build1;
		} else {
			return $build2;
		}
	}
	function getLatestFromBuilds($builds) {
		if (count($builds) > 0) {
			$latest = $builds[0];
			foreach($builds as $build) {
				$latest = $this->getLatest($latest,$build);
			}
			return $latest;
		} else {
			return null;
		}
	}
	function getBuilds($includeexperimental = false) {
		$builds = array();
		foreach($this->entries as $entry) {
			$build = new LimegreenBuild($entry);
			if ($includeexperimental || !$build->experimental) {
				$builds[] = $build;
			}
		}
		return $builds;
	}
	function getLatestBuild($includeexperimental = false) {
		return $this->getLatestFromBuilds($this->getBuilds($includeexperimental));
	}
	function getExperimental() {
		$builds = array();
		foreach($this->entries as $entry) {
			$build = new LimegreenBuild($entry);
			if ($build->experimental) {
				$builds[] = $build;
			}
		}
		return $builds;
	}
	function getLatestExperimental() {
		return $this->getLatestFromBuilds($this->getExperimental());
	}
}

class LimegreenBuild {
	function __construct ($entry) {
		//$this->entry = $entry;
		$children = $entry->children(LIMEGREEN_NAMESPACE);
		$this->build = strval($children->build);
		$this->version = strval($children->version);
		$this->experimental = strval($children->experimental) == 'true';
		$this->content = strval($entry->content);
		$this->title = strval($entry->title);
		$foramt = 'Y-m-dTH:i:sZ';
		$this->updated = date(strval($entry->updated));
		$links = $entry->link;
		foreach ($links as $link) {
			$att = $link->attributes();
			if ($att->rel == 'enclosure') {
				$href = strval($att->href);
				if (strrpos($href,'-source-') > strrpos($href,"/")) {
					$this->source = $href;
				} elseif (strrpos($href,'-other-') > strrpos($href,"/")) {
					$this->other = $href;
				} elseif (strrpos($href,'-win-') > strrpos($href,"/")) {
					$this->windows = $href;
				} elseif (strrpos($href,'-mac-') > strrpos($href,"/")) {
					$this->maczip = $href;
				} elseif (strrpos($href,'.dmg') > strrpos($href,"/")) {
					$this->mac = $href;
				}
				if ($this->mac == '') {
					$this->mac = $this->maczip;
				}
			}
		}
	}
}

?>