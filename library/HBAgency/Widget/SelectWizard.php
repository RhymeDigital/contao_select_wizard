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
 * Run in a custom namespace, so the class can be replaced
 */
namespace HBAgency\Widget;


/**
 * Class SelectWizard
 *
 * Provides a widget with a single select menu
 * @copyright  HBAgency 2015
 * @author	   Blair Winans <bwinans@hbagency.com>
 * @author		Adam Fisher <afisher@hbagency.com>
 * @package    SelectWizard
 */
class SelectWizard extends \ModuleWizard
{
	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$this->import('Database');

		$arrButtons = array('edit', 'copy', 'delete', 'enable', /*'drag',*/ 'up', 'down');
		$strCommand = 'cmd_' . $this->strField;

		// Change the order
		if (\Input::get($strCommand) && is_numeric(\Input::get('cid')) && \Input::get('id') == $this->currentRecord)
		{
			switch (\Input::get($strCommand))
			{
				case 'copy':
					$this->varValue = array_duplicate($this->varValue, \Input::get('cid'));
					break;

				case 'up':
					$this->varValue = array_move_up($this->varValue, \Input::get('cid'));
					break;

				case 'down':
					$this->varValue = array_move_down($this->varValue, \Input::get('cid'));
					break;

				case 'delete':
					$this->varValue = array_delete($this->varValue, \Input::get('cid'));
					break;
			}
		}

		// Get all items of the selected table
		$objItems = $this->Database->prepare("SELECT id, ". $this->selectField ." AS `item` FROM ". $this->selectTable ." ORDER BY ". $this->selectField)->execute();
        
        $items = array();
        
		if ($objItems->numRows)
		{
			$items = $objItems->fetchAllAssoc();
		}

		// Add the module type (see #3835)
		foreach ($items as $k=>$v)
		{
			$v['type'] = $GLOBALS['TL_LANG']['FMD'][$v['type']][0];
			$items[$k] = $v;
		}

		$objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
								 ->limit(1)
								 ->execute($this->currentRecord);

		// Get the new value
		if (\Input::post('FORM_SUBMIT') == $this->strTable)
		{
			$this->varValue = \Input::post($this->strId);
		}

		// Make sure there is at least an empty array
		if (!is_array($this->varValue) || !$this->varValue[0])
		{
			$this->varValue = array('');
		}
		else
		{
			/*$arrCols = array();

			// Initialize the sorting order
			foreach ($cols as $col)
			{
				$arrCols[$col] = array();
			}

			foreach ($this->varValue as $v)
			{
				$arrCols[$v['col']][] = $v;
			}

			$this->varValue = array();

			foreach ($arrCols as $arrCol)
			{
				$this->varValue = array_merge($this->varValue, $arrCol);
			}*/
		}

		// Save the value
		if (\Input::get($strCommand) || \Input::post('FORM_SUBMIT') == $this->strTable)
		{
			$this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
						   ->execute(serialize($this->varValue), $this->currentRecord);

			// Reload the page
			if (is_numeric(\Input::get('cid')) && \Input::get('id') == $this->currentRecord)
			{
				$this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', \Environment::get('request'))));
			}
		}

		// Initialize the tab index
		if (!\Cache::has('tabindex'))
		{
			\Cache::set('tabindex', 1);
		}

		$tabindex = \Cache::get('tabindex');

		// Add the label and the return wizard
		$return = '<table id="ctrl_'.$this->strId.'" class="tl_selectwizard">
  <thead>
  <tr>
    <th>'.$GLOBALS['TL_LANG']['MSC']['sw_option'].'</th>
    <th>&nbsp;</th>
  </tr>
  </thead>
  <tbody class="sortable" data-tabindex="'.$tabindex.'">';

		// Add the input fields
		for ($i=0, $c=count($this->varValue); $i<$c; $i++)
		{
			$options = '';

			// Add modules
			foreach ($items as $j=>$v)
			{
				$options .= '<option value="'.specialchars($v['id']).'"'.static::optionSelected($v['id'], ((isset($this->varValue[$i]['item']) && $this->varValue[$i]['item']) ? $this->varValue[$i]['item'] : '')).'>'.$v['item'].'</option>';
			}

			$return .= '
  <tr>
    <td><select name="'.$this->strId.'['.$i.'][item]" class="tl_select tl_chosen" tabindex="'.$tabindex++.'" onfocus="BackendSelectWizard.getScrollOffset()" onchange="BackendSelectWizard.updateSelectLink(this)">'.$options.'</select></td><td>';

			// Add buttons
			foreach ($arrButtons as $button)
			{
				//$class = ($button == 'up' || $button == 'down') ? ' class="button-move"' : '';

				if ($button == 'edit')
				{
					//$return .= ' <a href="contao/main.php?do=themes&amp;table='.$this->selectTable.'&amp;act=edit&amp;id=' . (isset($this->varValue[$i]['item']) && $this->varValue[$i]['item']) . '&amp;popup=1&amp;rt=' . REQUEST_TOKEN . '&amp;nb=1" title="' . specialchars($GLOBALS['TL_LANG']['tl_layout']['edit_module']) . '" class="select_link" ' . (((isset($this->varValue[$i]['item']) && $this->varValue[$i]['item'])) ? '' : ' style="display:none"') . ' onclick="Backend.openModalIframe({\'width\':768,\'title\':\'' . specialchars(str_replace("'", "\\'", $GLOBALS['TL_LANG']['tl_layout']['edit_module'])) . '\',\'url\':this.href});return false">'.\Image::getHtml('edit.gif').'</a>' . \Image::getHtml('edit_.gif', '', 'class="select_image"' . (((isset($this->varValue[$i]['item']) && $this->varValue[$i]['item'])) ? ' style="display:none"' : ''));
				}
				elseif ($button == 'drag')
				{
					$return .= ' ' . \Image::getHtml('drag.gif', '', 'class="drag-handle" onmouseup="BackendSelectWizard.selectWizard(this, \'drag\',\'ctrl_'.$this->strId.'\');" title="' . sprintf($GLOBALS['TL_LANG']['MSC']['move']) . '"');
				}
				elseif ($button == 'enable')
				{
					$return .= ' ' . \Image::getHtml(((isset($this->varValue[$i]['enable']) && $this->varValue[$i]['enable']) ? 'visible.gif' : 'invisible.gif'), '', 'class="mw_enable" title="' . sprintf($GLOBALS['TL_LANG']['MSC']['mw_enable']) . '"') . '<input name="'.$this->strId.'['.$i.'][enable]" type="checkbox" class="tl_checkbox mw_enable" value="1" tabindex="'.$tabindex++.'" onfocus="Backend.getScrollOffset()"'. ((isset($this->varValue[$i]['enable']) && $this->varValue[$i]['enable']) ? ' checked' : '').'>';
				}
				else
				{
					$return .= ' <a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'"' . $class . ' title="'.specialchars($GLOBALS['TL_LANG']['MSC']['mw_'.$button]).'" onclick="BackendSelectWizard.selectWizard(this,\''.$button.'\',\'ctrl_'.$this->strId.'\');return false">'.\Image::getHtml($button.'.gif', $GLOBALS['TL_LANG']['MSC']['mw_'.$button], 'class="tl_listwizard_img"').'</a>';
				}
			}

			$return .= '</td>
  </tr>';
		}

		// Store the tab index
		\Cache::set('tabindex', $tabindex);

		return $return.'
  </tbody>
  </table>';
	}
}
