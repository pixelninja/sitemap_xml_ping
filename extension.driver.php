<?php

	require_once(TOOLKIT . '/class.gateway.php');
	require_once(TOOLKIT . '/class.sectionmanager.php');

	class Extension_Sitemap_Xml_Ping extends Extension {

		private static $config_handle = 'sitemap-xml-ping';

		public function about() {
			return array(
				'name'			=> 'Sitemap XML Ping',
				'version'		=> '1.1',
				'release-date'	=> '2017-07-15',
				'author'		=> array(
					array(
						'name' => 'Phill Gray',
						'email' => 'phill@thebold.nz'
					),
				),
				'description'	=> 'Notify Google/Bing when entries in a section relating to the sitemap is updated automatically.'
	 		);
		}

		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/system/preferences/',
					'delegate'	=> 'AddCustomPreferenceFieldsets',
					'callback'	=> 'addCustomPreferenceFieldsets'
				),
				array(
					'page' => '/publish/new/',
					'delegate' => 'EntryPostCreate',
					'callback' => 'entryPostEdit'
				),
				array(
					'page' => '/publish/edit/',
					'delegate' => 'EntryPostEdit',
					'callback' => 'entryPostEdit'
				)
			);
		}

	/*-------------------------------------------------------------------------
		Utilities
	-------------------------------------------------------------------------*/

		public function get($key) {
			return Symphony::Configuration()->get($key, self::$config_handle);
		}

	/*-------------------------------------------------------------------------
		Delegate Callbacks:
	-------------------------------------------------------------------------*/

		public function addCustomPreferenceFieldsets($context) {
			$wrapper = $context['wrapper'];

			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', 'Sitemap XML Ping'));

			$group = new XMLElement('div');
			$group->setAttribute('class', 'group');

			// Monitor sections
			$options = array();
			$sectionManager = new SectionManager($this->_Parent);
			$sections = $sectionManager->fetch();
			foreach($sections as $section) {
				$options[] = array($section->get('id'), $section->get('id') == $this->get('ping_sections'), $section->get('name'));
			}

			$label = Widget::Label(__('Monitor sections'));
			$input = Widget::Select('settings[' . self::$config_handle . '][ping_sections]', $options);
			//$input = Widget::Select('settings[' . self::$config_handle . '][]', $options, array('multiple' => 'multiple'));

			$label->appendChild($input);
			$group->appendChild($label);

			// Send this URL
			$label = Widget::Label(__('Ping URL'));
			//$input = Widget::Input('settings[sitemap_xml_ping_url]', URL.'/sitemap.xml', null, array('readonly' => 'readonly'));
			$input = Widget::Input('settings[' . self::$config_handle . '][ping_url]', URL.'/sitemap.xml', null, array('readonly' => 'readonly'));
			$label->appendChild($input);

			$group->appendChild($label);

			$fieldset->appendChild($group);

			$group = new XMLElement('div');

			// access key so curl can write to sitemap.xml
			$label = Widget::Label(__('Access Token'));
			$input = Widget::Input('settings[' . self::$config_handle . '][access_token]', $this->get('access_token'));
			$label->appendChild($input);

			$group->appendChild($label);

			$fieldset->appendChild($group);
			$fieldset->appendChild(
				new XMLElement('p', 'When new entries are created in the selected section, a request will be sent to Google with the Ping URL which should be an RSS, Atom or RDF feed.', array('class' => 'help'))
			);

			$wrapper->appendChild($fieldset);
		}

		public function entryPostEdit(Array &$context) {
			// Store url and section in variables
			$sections = $this->get('ping_sections');
			$url = $this->get('ping_url');
			$token = $this->get('access_token');

			// Check the Entry is being edited in the right section, otherwise return
			if($context['section']->get('id') != $sections) return;

			// Make sure a Ping URL is set.
			if(is_null($url)) return;

			// Make sure a Token is set.
			if(is_null($token)) return;

			// Update sitemap first
			$s = new Gateway;
			$s->init(URL . '/symphony/extension/sitemap_xml/raw/?auth-token='.$token);

			// Catch the result.
			$s_result = $s->exec();
			if(isset(Symphony::$Log)) Symphony::$Log->pushToLog(__('Sitemap XML: ') . $s_result, E_USER_NOTICE, true);
			$s_info = $s->getInfoLast();

			// Google
			$g = new Gateway;
			$g->init('http://www.google.com/webmasters/tools/ping?sitemap=' . $url);

			$g_result = $g->exec();
			if(isset(Symphony::$Log)) Symphony::$Log->pushToLog(__('Sitemap XML Ping: (Google) ') . $g_result, E_USER_NOTICE, true);
			$g_info = $g->getInfoLast();


			// Bing
			$b = new Gateway;
			$b->init('http://www.bing.com/webmaster/ping.aspx?siteMap=' . $url);

			$b_result = $b->exec();
			if(isset(Symphony::$Log)) Symphony::$Log->pushToLog(__('Sitemap XML Ping: (Bing) ') . $b_result, E_USER_NOTICE, true);
			$b_info = $b->getInfoLast();

			return $g_info['http_code'] == 200 && $b_info['http_code'] == 200;
		}
	}
