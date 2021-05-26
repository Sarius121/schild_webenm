<?php

namespace ENMLibrary;

class Modal{

    private const HTML_START = '<div id="';
    private const HTML_AFTER_ID = '" class="modal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">';
    private const HTML_AFTER_TITLE = '</h5><button type="button" class="close" data-dismiss="modal" aria-label="Schließen"><span aria-hidden="true">&times;</span></button></div><div class="modal-body">';
    private const HTML_AFTER_BODY = '</div><div class="modal-footer">';
    private const HTML_END = '</div></div></div></div>';

    private const DEFAULT_BUTTONS = [["name" => "Abbrechen", "layer" => "btn-secondary", "closeOnClick" => true, "onclick" => null],
                                    ["name" => "OK", "layer" => "btn-primary", "closeOnClick" => false, "onclick" => null]];

    private $id;
    private $title;
    private $body;
    private $buttons = [];

    public function __construct($id, $title, $body=null, $buttons=[]) {
        $this->id = $id;
        $this->title = $title;
        $this->body = $body;
        $this->buttons = $buttons;
    }

    public function setBody($body){
        $this->body = $body;
    }

    public function setTitle($title){
        $this->title = $title;
    }

    public function setID($id){
        $this->id = $id;
    }

    public static function defaultModal($id, $title, $onOKClicked, $body=null){
        $modal = new self($id, $title, $body);
        $modal->defaultButtons($onOKClicked);
        return $modal;
    }

    public function addButton($name, $btnLayer="btn-primary", $closeOnClick=true, $onclick=null) {
        $this->buttons[] = array("name" => $name, "layer" => $btnLayer, "closeOnClick" => $closeOnClick, "onclick" => $onclick);
    }

    public function setButtons($buttons) {
        $this->buttons = $buttons;
    }

    private function defaultButtons($onclick) {
        $this->buttons = Modal::DEFAULT_BUTTONS;
        $this->buttons[1]["onclick"] = $onclick;
    }

    private function getButtonsHTML(){
        $html = "";
        foreach($this->buttons as $button){

            $html .= '<button type="button" class="btn ' . $button["layer"] . '"';
            if($button["onclick"] != null){
                $html .= ' onclick="' . $button["onclick"] . '"';
            }
            if($button["closeOnClick"] != null){
                $html .= ' data-dismiss="modal"';
            }
            $html .= '>' . $button["name"] . '</button>';
        }
        return $html;
    }

    public function getHTML()
    {
        $html = Modal::HTML_START;
        $html .= $this->id;
        $html .= Modal::HTML_AFTER_ID;
        $html .= $this->title;
        $html .= Modal::HTML_AFTER_TITLE;
        $html .= $this->body;
        $html .= Modal::HTML_AFTER_BODY;
        $html .= $this->getButtonsHTML();
        $html .= Modal::HTML_END;
    }

    public function getHTMLBeforeBody(){
        $html = Modal::HTML_START;
        $html .= $this->id;
        $html .= Modal::HTML_AFTER_ID;
        $html .= $this->title;
        $html .= Modal::HTML_AFTER_TITLE;
        return $html;
    }

    public function getHTMLAfterBody(){
        $html = Modal::HTML_AFTER_BODY;
        $html .= $this->getButtonsHTML();
        $html .= Modal::HTML_END;
        return $html;
    }

}

?>