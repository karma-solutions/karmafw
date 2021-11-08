<?php

namespace App\Controllers;

use \KarmaFW\Routing\Controllers\WebAppController;


class ErrorController extends WebAppController
{    
    
    /**
     * Affiche une page d'erreur 404
     *
     * @param  array $arguments
     * @return void
     */
    public function error404($arguments=[])
    {
        /*
        # Add this line at the end of routes.php to catch unknown routes :
        Router::error404(['\\App\\Controllers\\ErrorController', 'error404']);
        */
        $this->template->assign('title', 'Error 404');
        $this->template->assign('message', 'The specified page does not exist');

        return $this->showError404("Page introuvable", "La page demandée n'existe pas");
    }

}
