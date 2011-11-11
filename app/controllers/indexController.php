<?

class indexController extends Crayd_Controller {

    public function indexAction() {
        $this->view->var = 'World!';
    }
    
}