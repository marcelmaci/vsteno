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
      public function __construct() {
        $this->PreTag = "";
        $this->PreHTML = "";
        $this->BareWord = "";
        $this->PostTag = "";
        $this->PostHTML = "";
      }
      */
}

class Processed {
        public $ActualMetaform = "";
        public $StandardShorthand = "";
        public $PrintableShorthand = "";
}

class SingleWord {
    public $Original = "";
    public $Splitted = null;
    public $Processed = null;
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
    }
}

?>
