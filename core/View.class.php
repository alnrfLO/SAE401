<?php
class View {
    protected $data;
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    public function render() {
        return '';
    }
}