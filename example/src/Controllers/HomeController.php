<?php

namespace App\Controllers;

use \KarmaFW\Routing\Controllers\WebAppController;


class HomeController extends WebAppController
{

    public function homepage($arguments=[])
    {
        $this->template->display('homepage.tpl');
    }

}
