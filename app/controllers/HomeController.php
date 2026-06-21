<?php
class HomeController extends BaseController
{
    public function index()
    {
        $stats = Report::stats();
        $reports = Report::all();
        $successStories = Report::successStories();
        $this->view('home/index', compact('stats', 'reports', 'successStories'));
    }

    public function about()
    {
        $this->view('home/about');
    }

    public function successStories()
    {
        $stories = Report::successStories();
        $this->view('home/success', compact('stories'));
    }
}
