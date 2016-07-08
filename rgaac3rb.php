<?php

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
jimport('joomla.registry.registry');

class plgAjaxRgaac3rb extends JPlugin
{

	public function onAjaxRgaac3rb()
	{
		$action = JFactory::getApplication()->input->getString('action', null);

		if (!is_null($action) && method_exists(__CLASS__, 'action' . ucfirst($action)))
		{
			call_user_func(array(__CLASS__, 'action' . ucfirst($action)));
		}

		echo new JResponseJson(null, null, true);
		JFactory::getApplication()->close();
	}

	private function actionImport()
	{
		jimport('joomla.filesystem.file');

		$files = $_FILES;
		if (count($files) != 1)
		{
			echo new JResponseJson(null, JText::_('PLG_AJAX_RGAAC3RB_IMPORT_ERR_POST'), true);
			JFactory::getApplication()->close();
		}

		$templateId = JFactory::getApplication()->input->getInt('templateId', 0);
		if ($templateId == 0)
		{
			echo new JResponseJson(null, JText::_('PLG_AJAX_RGAAC3RB_IMPORT_ERR_PARAMS'), true);
			JFactory::getApplication()->close();
		}

		$file = $files[0];
		if (JFile::getExt($file['name']) != 'json')
		{
			echo new JResponseJson(null, JText::_('PLG_AJAX_RGAAC3RB_IMPORT_ERR_EXT'), true);
			JFactory::getApplication()->close();
		}

		$tmp_path = JFactory::getConfig()->get('tmp_path');
		if (JFile::upload($file['tmp_name'], $tmp_path . '/' . $file['name']) == false)
		{
			echo new JResponseJson(null, JText::_('PLG_AJAX_RGAAC3RB_IMPORT_ERR_UPLOAD'), true);
			JFactory::getApplication()->close();
		}

		$params = file_get_contents($tmp_path . '/' . $file['name']);
		JFile::delete($tmp_path . '/' . $file['name']);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$fields = array(
		    $db->quoteName('params') . ' = ' . $db->quote($params)
		);

		$conditions = array(
		    $db->quoteName('id') . ' = ' . $db->quote($templateId),
		    $db->quoteName('client_id') . ' = 0'
		);

		$query->update($db->quoteName('#__template_styles'))->set($fields)->where($conditions);
		$db->setQuery($query)->execute();

		echo new JResponseJson();
		JFactory::getApplication()->close();
	}

}
