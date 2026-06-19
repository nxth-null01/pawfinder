<?php
class HomeController extends BaseController { public function index(){ $stats=Report::stats(); $reports=Report::all(); $this->view('home/index', compact('stats','reports')); } }
