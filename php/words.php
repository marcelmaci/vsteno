<?php 

require_once "constants.php";
require_once "data.php";
require_once "parser.php";
require_once "engine.php";
require_once "session.php";

class Splitted {
      public $PreTag = "";
      public $PreHTML = "";
      public $BareWord = "";
      public $PostTag = "";
      public $PostHTML = "";
      /*
      public function __construct( $pretag, $prehtml, $word, $posttag, $posthtml ) {
        $this->PreTag = $pretag;
        $this->PreHTML = $prehtml;
        $this->BareWord = $word;
        $this->PostTag = $posttag;
        $this->PostHTML = $posthtml;
    }
    */
}

class Processed {
        public $ActualMetaform = "";
        public $StandardShorthand = "";
        public $PrintableShorthand = "";
        function __construct( $word ) {
            $this->PrintableShorthand = "blabla"; //MetaParser( $word );
        }
}

class SingleWord {
    public $Original = "";
    public $Splitted = null;
    public $Processed = null;
    public $Splines = null;
    public $SVG = "";
    public function SetOriginalWord( $word ) {
        $this->Original = $word;
    }
    public function GetOriginalWord() {
        return $this->Original;
    }
    public function InsertSplitted( $splitted ) {
        $this->Splitted = $splitted;
    }
    public function InsertProcessed( $processed ) {
        $this->Processed = $processed;
    }
    public function __construct( $word = "" ) {
        $this->Original = $word;
        //$temp = new Processed( "hardcoded" );         // how do I insert an instance of Processed ?!?!?
        //$this->Processed = new Processed( $word );
        //$this->Processed = $temp;
        //$this-InsertProcessed( new Processed( $word ));
   
    }
}

?>
