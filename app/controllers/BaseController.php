<?php

class BaseController extends Controller
{
    /**
     * Message bag.
     *
     * @var Illuminate\Support\MessageBag
     */
    protected $messageBag = null;

    /**
     * Initializer.
     *
     * @return void
     */
    public function __construct()
    {
        // CSRF Protection
        $this->beforeFilter('csrf', array('on' => 'post'));

        //
        $this->messageBag = new Illuminate\Support\MessageBag;

        $this->composer();
    }

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if ( ! is_null($this->layout)) {
            $this->layout = View::make($this->layout);
        }
    }

    protected function composer()
    {
        View::composer('backend/layouts/default', function($view) {
            $view->with('roles', Role::where('id', '!=', '1')->lists('role', 'id'));
        });
    }

}
