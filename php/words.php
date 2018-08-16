<?php 

/*
        OOP-part of VSTENO: the original idea was to write a oop-wrapper for VSTENO, which means that the whole program will be procedural, but one class (in this case ShorthandWord)
        allows programmers to access functions of VSTENO easily via object-oriented programming.
        This approach is certainly possible (and works rudimentarily in the following implementation), but (1) it adds nothing to the basic functionality of VSTENO and 
        (2) it is not my priority for the moment (there are other functionalities with higher priority - or should I say: urgence ... ;-) - pending).
        So, I'll leave this part of code as it is for the moment.
       
        Actual state: a part from Parser (pre/post-inline/html-tags and $Original, $BareWord and $PrtShort) nothing works!
*/


require_once "constants.php";
require_once "data.php";
require_once "parser.php";
require_once "engine.php";
require_once "session.php";

class WordTags {
      public $PreTags = "";              
      public $PreHTMLTags = "";
      public $PostTags = "";
      public $PostHTMLTags = "";
}

class WordFormats extends WordTags {
      public $Original = "";      // original word coming directly vom original text (may contain html/inline-pre/post-tags)
      public $BareWord = "";      // just the word (without tags)
      public $StdShort = "";      // word in "standard shorthand notation" (i.e. before transcriptor and substituter)              => example:    [SP]ILT{EN}
      public $PrtShort = "";      // word in "printable shorthand notation" (i.e. ready to be "printed" inserting stenotokens)     => example:    [SP]IL[&T][&E][EN]
        
      function __construct( $word = "" ) {
            global $combined_pretags, $html_pretags, $combined_posttags, $html_posttags;    
            $this->Original = $word;
            $this->BareWord = GetWordSetPreAndPostTags( $word );
            $this->PreTags = $combined_pretags;
            $this->PostTags = $combined_posttags;
            $this->PrtShort = MetaParser( $word );
      }
}

class ShorthandWord extends WordFormats {
      public $TokenList = array();
      public $Splines = array();
      public $SVGString = "";
      function __construct( $word = 0 ){
            parent::__construct( $word );
            $this->PreHTMLTags = ParseAndSetInlineOptions( $this->PreTags );
            $this->TokenList = Metaform2TokenList( $this->PrtShort );
            //$this->SVGString = TokenList2SVGWithSessionVariables( $this->TokenList );
            $this->PostHTMLTags = ParseAndSetInlineOptions( $this->PostTags );
      }
}

?>
