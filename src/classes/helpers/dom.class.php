<?php
/**
* This file contains DOM-functions
*/

class DOM extends DOMElement {

	function __construct() {}


	// create DOM object from HTML string
	function createDOM($html_string) {
		
		// create new dom document
		$dom = new DOMDocument("1.0", "UTF-8");

		// prepare for <br> issues in PHP DOM
		// I cannot load document with <br> tags and when I save HTML it automatically replaces all <br /> with <br> which I then again cannot load.
		$html_string = preg_replace("/<br>/", "<br />", $html_string);


		// Double encode any already encoded entity, before encoding all characters (to enable full switch back)
		// We don't want to encode the "real" HTML, so htmlentities is too much
		$html_string = preg_replace("/&([#0-9a-zA-Z]{2,6};)/", "&amp;$1", $html_string);
		// convert entities to avoid broken chars
		// - the charset handling of the PHP Dom documents seems to be a bit indecisive
		$html_string = mb_convert_encoding($html_string, "HTML-ENTITIES", "UTF-8");


		// ensure correct document creation on html fragments
		// missing body tag
		if(!preg_match("/\<body/", $html_string)) {
			$html_string = '<body>'.$html_string.'</body>';
		}

		// missing head tag
		if(!preg_match("/\<head/", $html_string)) {
			$html_string = '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>'.$html_string;
		}

		// missing html tag
		if(!preg_match("/\<html/", $html_string)) {
			$html_string = '<html>'.$html_string.'</html>';
		}


		// no strict error checking - PHP DOMDocument still doesn't really understand HTML5
		$dom->strictErrorChecking = false;
 
		// loadHTML needs content definition for UTF-8 (a meta tag)
		// - it should be enough to state it in the constructor, but it does not work
		if(@$dom->loadHTML($html_string)) {

			// Map head and body tag to dom (similarly to real DOM)
			$dom->head = $dom->getElementsByTagName("head") ? $dom->getElementsByTagName("head")->item(0) : false;
			$dom->body = $dom->getElementsByTagName("body") ? $dom->getElementsByTagName("body")->item(0) : false;

			return $dom;
	
		}

		return false;
	}

	// Check if DOM node has given classname
	function hasClassname($node, $classname) {

		$class_attribute = $node->getAttribute("class");
		if($class_attribute) {
			return preg_match("/(^|\b)+(".$classname.")($|\b)/", $class_attribute);
		}

		return false;
	}

	// get first element which matches $identifier (simple CSS selector)
	function getElement($dom, $identifier) {

//		print "LOOK FOR:".$identifier."<br>\n";
		$tag = false;
		$classnames = [];
		$id = false;

		// get tag
		preg_match("/^([a-zA-Z1-9]+)/", $identifier, $tag_match);
//		print_r($tag_match);
		if($tag_match) {
			$tag = strtolower($tag_match[1]);

			// remove tag from identifier
			$identifier = preg_replace("/$tag/", "", $identifier);
		}

		// get id
		preg_match("/#([a-zA-Z1-9]+)/", $identifier, $id_match);
//		print_r($id_match);
		if($id_match) {
			$id = $id_match[1];

			// remove id from identifier
			$identifier = preg_replace("/#$id/", "", $identifier);
		}

		// get classnames
		preg_match_all("/\.([a-zA-Z1-9]+)/", $identifier, $class_matches);
//		print_r($class_matches);
		if($class_matches) {
			$classnames = $class_matches[1];
		}

		$all_nodes = $dom->getElementsByTagName("*");
		foreach($all_nodes as $node) {

			if(!$tag || strtolower($node->nodeName) == $tag) {
				
				if(!$id || $node->getAttribute("id") == $id) {

					$classnames_ok = true;

					// check that all classnames match the current node
					foreach($classnames as $classname) {
						// if one is missing, node doesn't match
						if(!$this->hasClassname($node, $classname)) {
							$classnames_ok = false;
							break;
						}
					}

					// is node a full match
					if($classnames_ok) {
						return $node;
					}

				}

			}

		}

		return false;
	}



	// get all elements which matches $identifier (simple CSS selector)
	function getElements($dom, $identifier) {

//		print "LOOK FOR:".$identifier."<br>\n";

		$nodes = [];

		$tag = false;
		$classnames = [];
		$id = false;

		// get tag
		preg_match("/^([a-zA-Z1-9]+)/", $identifier, $tag_match);
//		print_r($tag_match);
		if($tag_match) {
			$tag = strtolower($tag_match[1]);

			// remove tag from identifier
			$identifier = preg_replace("/$tag/", "", $identifier);
		}

		// get 
		preg_match("/#([a-zA-Z1-9]+)/", $identifier, $id_match);
//		print_r($id_match);
		if($id_match) {
			$id = $id_match[1];

			// remove id from identifier
			$identifier = preg_replace("/#$id/", "", $identifier);
		}

		preg_match_all("/\.([a-zA-Z1-9]+)/", $identifier, $class_matches);
//		print_r($class_matches);
		if($class_matches) {
			$classnames = $class_matches[1];
		}

		$all_nodes = $dom->getElementsByTagName("*");
		foreach($all_nodes as $node) {

			if(!$tag || strtolower($node->nodeName) == $tag) {
				
				if(!$id || $node->getAttribute("id") == $id) {

					$classnames_ok = true;

					// check that all classnames match the current node
					foreach($classnames as $classname) {
						// if one is missing, node doesn't match
						if(!$this->hasClassname($node, $classname)) {
							$classnames_ok = false;
							break;
						}
					}

					// is node a full match
					if($classnames_ok) {
						$nodes[] = $node;
					}

				}

			}

		}

		return $nodes;
	}



	// innerHTML equivalent
	function innerHTML($node, $content) {
		while($node->childNodes->length) {
			$node->removeChild($node->firstChild);
		}
		$node->appendChild(new DOMText($content));
	}

	// get manipulator classVar from node
	function classVar($node, $var_name) {

		$classname = $node->getAttribute("class");
		preg_match("/(^| )".$var_name.":([?=\w\/#~:.,?+=?&%@!\-]*)/", $classname, $match);
		if($match) {
			return $match[2];
		}

		return false;
	}



	// fix broken output from native saveHTML function
	function saveHTML($dom) {

		$html = $dom->saveHTML();

		// fix broken tags
		$html = preg_replace("/\<meta ([^\>]+)>/", "<meta $1 />", $html);
		$html = preg_replace("/\<link ([^\>]+)>/", "<link $1 />", $html);
		$html = preg_replace("/\<img ([^\>]+)>/", "<img $1 />", $html);
		$html = preg_replace("/\<br\>/", "<br />", $html);
		$html = preg_replace("/%7B/", "{", $html);
		$html = preg_replace("/%7D/", "}", $html);

		// convert entities back
		$html = mb_convert_encoding($html, "UTF-8", "HTML-ENTITIES");

		return $html;
	}

	// remove all occurences of tag from DOM node
	function removeAll($node, $tag) {

		$tags = $node->getElementsByTagName($tag);
		if($tags) {
			foreach($tags as $tag) {
				$tag->parentNode->removeChild($tag);
			}
		}

	}

	// remove all occurences of attributes from tags in DOM node
	function stripAttributes($node) {

		$nodes = $node->getElementsByTagName('*');

		// loop nodes
		foreach($nodes as $node) {

			// remember what to remove and remove in the end of each iteration as removing alters the node and thus the loop
				
			$remove_attributes = array();
			// loop attributes
			foreach($node->attributes as $attribute => $attribute_node) {

				// check for allowed attribute
				if(preg_match("/href|class|width|height|alt|charset/i", $attribute)) {

					// if href, only allow absolute http links (no javascript or other crap)
					// if($attribute == "href" && strpos($attribute_node->value, "http://") !== 0) {
					if($attribute == "href" && !preg_match("/^((http[s]?\:\/)?\/|mailto\:|tel\:)/", $attribute_node->value, $match)) {
						$remove_attributes[] = $attribute;
					}
				}
				else {
					$remove_attributes[] = $attribute;
				}
			}
			// remove identified attributes
			foreach($remove_attributes as $remove_attribute) {
				$node->removeAttribute($remove_attribute);
			}

		}

	}

	// Get simple-formatted plain text version of DOM object
	// Removes non-text nodes,
	// Replaces a-tags with text [href] strings
	// Iterates all common text nodes to construct meaningful and well-formatted plain text version
	function getFormattedTextFromDOM($dom) {

		$text = "";

		// remove non-text nodes
		$this->removeAll($dom, "head");
		$this->removeAll($dom, "script");
		$this->removeAll($dom, "style");
		$this->removeAll($dom, "link");

		// convert a-tag nodes to text + [link]
		$a_tags = $dom->getElementsByTagName("a");
		if($a_tags) {
			while($a_tags->length) {
				$a_tag = $a_tags->item(0);
				$a_tag->parentNode->replaceChild($dom->createTextNode($a_tag->textContent." [".$a_tag->getAttribute("href")."]"), $a_tag);
			}
		}

		// get current body and start compiling text string from HTML content
		$body = $dom->getElementsByTagName("body");
		if($body) {
			$text = $this->iterateTextNodes($body->item(0));
		}

		return $text;
	}

	// Iterate all texts node contained within $node and return trimmed and wellformatted textContent
	function iterateTextNodes($node) {

		$text = "";

		foreach($node->childNodes as $child_node) {

			// just ignore empty text nodes (and NBSPs)
			// decoding nbsp-entity is the only way I found to safely replace NBSPs
			if(trim(preg_replace("/( |".html_entity_decode("&nbsp;").")/", "", $child_node->textContent))) {

				// TEXT fragment
				if($child_node->nodeName == "#text") {
					// trim and remove tabs
					$text .= trim(preg_replace("/\t/", "", $child_node->textContent))."\n\n";
				}
				// only include node text if it is not specified to be ignored
				else if(!$this->hasClassname($child_node, "ignore_text")) {

					// if node has text-children, continue to look at their content, to enable correct formatting
					if($this->getTextChildNodes($child_node)) {
						// recusive call to self
						$text .= $this->iterateTextNodes($child_node);
					}
					else {
						// trim and remove tabs
 						$text .= trim(preg_replace("/\t/", "", $child_node->textContent))."\n\n";
					}
				}
			} 
		}

		return $text;
	}

	// Get all common text childnodes from node
	function getTextChildNodes($node) {
		$text_node_types = ["h1", "h2", "h3", "h4", "h5", "p", "li", "dt", "dd", "td"];	
		$text_nodes = [];

		// query all nodes and then filter them, to get content in the right order
		$childNodes = $node->getElementsByTagName("*");
		foreach($childNodes as $node) {
			if(array_search($node->nodeName, $text_node_types) !== false) {
				$text_nodes[] = $node;
			}
		
		}

		return $text_nodes;
	}

}