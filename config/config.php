<?php

/**
 * Copyright (C) 2014 HB Agency
 * 
 * @author		Blair Winans <bwinans@hbagency.com>
 * @author		Adam Fisher <afisher@hbagency.com>
 * @link		http://www.hbagency.com
 * @license		http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */



/**
 * Back end form fields
 */
$GLOBALS['BE_FFL']['select_wizard'] = 'HBAgency\Widget\SelectWizard';


/**
 * Back end css
 */
if(TL_MODE=='BE') {
    $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/select_wizard/assets/js/selectwizard.js';
    $GLOBALS['TL_CSS'][] = 'system/modules/select_wizard/assets/css/tl_selectwizard.css';
}