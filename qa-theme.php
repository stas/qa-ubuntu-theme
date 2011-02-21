<?php

/*
	Ubuntu Light Theme for Romanian LoCO
	
	File: qa-theme/Ubuntu/qa-theme.php
	Version: 0.1
	Date: February, 2011 (die-die Valentines Day!)
	Description: Override base theme to output xhtml and fix css for nginx


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/

	class qa_html_theme extends qa_html_theme_base
	{
	
		var	$indent=0;
		var $lines=0;
		
		var $rooturl;
		var $template;
		var $content;
		var $request;
		var $filters = array(
			'/HTML>(.*)/'			=> 'html>',
			'/HEAD>(.*)/'			=> 'head>',
			'/TITLE>/'				=> 'title>',
			'/SCRIPT>/'				=> 'script>',
			'/<SCRIPT TYPE=/'		=> '<script type=',
			'/<SCRIPT SRC=/'		=> '<script src=',
			'/<BODY/'				=> '<body ',
			'/BODY>/'				=> 'body>',
			'/<A HREF=/'			=> '<a href=',
			'/<A /'					=> '<a ',
			'/<\/A>/'				=> '</a>',
			'/ CLASS="/'			=> ' class="',
			'/ METHOD="GET" /'		=> ' method="get" ',
			'/ METHOD="POST" /'		=> ' method="post" ',
			'/ ACTION="/'			=> ' action="',
			'/ NAME="/'				=> ' name="',
			'/<SPAN /'				=> '<span ',
			'/<\/SPAN>/'			=> '</span>',
			'/ TITLE="/'			=> ' title="',
			'/<META HTTP-EQUIV="Content-type" CONTENT="text\/html; charset=utf-8"(.*)/' => '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" //>',
			'/(.*)Powered by Question2Answer(.*)/' => '',
		);
		
		function qa_html_theme($template, $content, $rooturl, $request)
	/*
		Initialize the object and assign local variables
	*/
		{
			$this->template=$template;
			$this->content=$content;
			$this->rooturl=$rooturl;
			$this->request=$request;
		}
		
		function filter_ugly_stuff( $string )
		{
			foreach( $this->filters as $search => $replace )
				$string = preg_replace( $search, $replace, $string );
			
			if( preg_match( '/<title/', $string ) )
				$this->output('<script type="text/javascript" src="'.$this->rooturl.'js/jquery.min.js?1.5.0"></script>');
			
			return $string;
		}
		
		function output_array($elements)
	/*
		Output each element in $elements on a separate line, with automatic HTML indenting.
		This should be passed markup which uses the <tag/> form for unpaired tags, to help keep
		track of indenting, although its actual output converts these to <tag> for W3C validation
	*/
		{
			foreach ($elements as $element) {
				$element = $this->filter_ugly_stuff( $element );
				$delta=substr_count($element, '<')-substr_count($element, '<!')-2*substr_count($element, '</')-substr_count($element, '/>');
				
				if ($delta<0)
					$this->indent+=$delta;
				
				echo str_repeat("\t", max(0, $this->indent)).str_replace('/>', '>', $element)."\n";
				
				if ($delta>0)
					$this->indent+=$delta;
					
				$this->lines++;
			}
		}

		
		function output() // other parameters picked up via func_get_args()
	/*
		Output each passed parameter on a separate line - see output_array() comments
	*/
		{
			$this->output_array(func_get_args());
		}

		
		function output_raw($html)
	/*
		Output $html at the current indent level, but don't change indent level based on the markup within.
		Useful for user-entered HTML which is unlikely to follow the rules we need to track indenting
	*/
		{
			if (strlen($html))
				echo str_repeat("\t", max(0, $this->indent)).$html."\n";
		}

		
		function output_split($parts, $class, $outertag='span', $innertag='span', $extraclass=null)
	/*
		Output the three elements ['prefix'], ['data'] and ['suffix'] of $parts (if they're defined),
		with appropriate CSS classes based on $class, using $outertag and $innertag in the markup.
	*/
		{
			if (empty($parts) && ($outertag!='TD'))
				return;
				
			$this->output(
				'<'.$outertag.' class="'.$class.(isset($extraclass) ? (' '.$extraclass) : '').'">',
				(strlen(@$parts['prefix']) ? ('<'.$innertag.' class="'.$class.'-pad">'.$parts['prefix'].'</'.$innertag.'>') : '').
				(strlen(@$parts['data']) ? ('<'.$innertag.' class="'.$class.'-data">'.$parts['data'].'</'.$innertag.'>') : '').
				(strlen(@$parts['suffix']) ? ('<'.$innertag.' class="'.$class.'-pad">'.$parts['suffix'].'</'.$innertag.'>') : ''),
				'</'.$outertag.'>'
			);
		}

		
		function finish()
	/*
		Post-output cleanup. For now, check that the indenting ended right, and if not, output a warning in an HTML comment
	*/
		{
			if ($this->indent)
				echo "<!--\nIt's no big deal, but your HTML could not be indented properly. To fix, please:\n".
					"1. Use this->output() to output all HTML.\n".
					"2. Balance all paired tags like <td>...</td> or <div>...</div>.\n".
					"3. Use a slash at the end of unpaired tags like <img/> or <input/>.\n".
					"Thanks!\n-->\n";
		}

		
	//	From here on, we have a large number of class methods which output particular pieces of HTML markup
	//	The calling chain is initiated from qa-index.php, or qa-ajax-vote.php for refreshing the voting box
	//	For most HTML elements, the name of the function is similar to the element's CSS class, for example:
	//	search() outputs <div class="qa-search">, q_list() outputs <div class="qa-q-list">, etc...

		function doctype()
		{
			$this->output('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
		}
		
		function head_css()
		{
			$this->output('<link rel="stylesheet" type="text/css" href="'.$this->rooturl.$this->css_name().'" //>');
			$this->output('<script type="text/javascript" src="'.$this->rooturl.'js/jquery.corner.js?2.11"></script>');
			$this->output('<link rel="stylesheet" type="text/css" href="'.$this->rooturl.'/js/prettify/prettify.css" //>');
			$this->output('<script type="text/javascript" src="'.$this->rooturl.'js/prettify/prettify.js"></script>');
		}
		
		function css_name()
		{
			return 'qa-styles.css?'.QA_VERSION;
		}

		function head_custom()
		{} // abstract method
		
		function body_content()
		{
			$this->body_prefix();
			
			$this->output('<div class="qa-body-wrapper">', '');

			$this->header();
			
			$this->output('<div class="qa-content">');
			
			$this->sidepanel();
			$this->main();
			
			$this->output('</div> <!-- END qa-content -->', '');
			
			$this->footer();
			
			$this->output('</div> <!-- END body-wrapper -->');
			
			$this->body_suffix();
		}
		
		function body_tags()
		{
			$class='qa-template-'.qa_html($this->template);
			
			if (isset($this->content['categoryid']))
				$class.=' qa-category-'.qa_html($this->content['categoryid']);
			
			$this->output('class="'.$class.'"');
		}

		function body_prefix()
		{} // abstract method

		function body_suffix()
		{} // abstract method

		function header()
		{
			$this->output('<div class="qa-header">');
			
			$this->logo();
			$this->nav_main_sub();
			$this->header_clear();
			
			$this->output('</div> <!-- END qa-header -->', '');
			
			$this->nav_user_search();
		}
		
		function nav_user_search()
		{
			$this->output('<div class="qa-sub-header">');
			$this->output('<div class="qa-sub-header-content">');
			
			$this->search();
			$this->nav('user');
			
			$this->output('</div> <!-- END qa-sub-header-content -->', '');
			$this->output('</div> <!-- END qa-sub-header -->', '');
		}
		
		function nav_main_sub()
		{
			$this->nav('main');
			$this->nav('sub');
		}
		
		function logo()
		{
			$this->output(
				'<div class="qa-logo">',
				$this->content['logo'],
				'</div>'
			);
		}
		
		function search()
		{
			$search=$this->content['search'];
			
			$this->output(
				'<div class="qa-search">',
				'<form '.$search['form_tags'].' >',
				@$search['form_extra']
			);
			
			$this->search_field($search);
			$this->search_button($search);
			
			$this->output(
				'</form>',
				'</div>'
			);
		}
		
		function search_field($search)
		{
			$this->output('<input '.$search['field_tags'].' value="'.@$search['value'].'" class="qa-search-field"/>');
		}
		
		function search_button($search)
		{
			$this->output('<input type="submit" value="'.$search['button_label'].'" class="qa-search-button"/>');
		}
		
		function nav($navtype)
		{
			$navigation=@$this->content['navigation'][$navtype];
			
			if (($navtype=='user') || !empty($navigation)) {
				$this->output('<div class="qa-nav-'.$navtype.'">');
				
				if ($navtype=='user')
					$this->logged_in();
					
				// reverse order of 'opposite' items since they float right
				foreach (array_reverse($navigation, true) as $key => $navlink)
					if (@$navlink['opposite']) {
						unset($navigation[$key]);
						$navigation[$key]=$navlink;
					}
					
				$this->nav_list($navigation, $navtype);
				$this->nav_clear($navtype);
	
				$this->output('</div>');
			}
		}
		
		function nav_list($navigation, $navtype)
		{
			$this->output('<ul class="qa-nav-'.$navtype.'-list">');

			foreach ($navigation as $key => $navlink)
				$this->nav_item($key, $navlink, $navtype);
			
			$this->output('</ul>');
		}
		
		function nav_clear($navtype)
		{
			$this->output(
				'<div class="qa-nav-'.$navtype.'-clear">',
				'</div>'
			);
		}
		
		function nav_item($key, $navlink, $navtype)
		{
			$this->output('<li class="qa-nav-'.$navtype.'-item'.(@$navlink['opposite'] ? '-opp' : '').' qa-nav-'.$navtype.'-'.$key.'">');
			$this->nav_link($navlink, $navtype);
			$this->output('</li>');
		}
		
		function nav_link($navlink, $navtype)
		{
			if (isset($navlink['url']))
				$this->output(
					'<a href="'.$navlink['url'].'" class="qa-nav-'.$navtype.'-link'.
					(@$navlink['selected'] ? (' qa-nav-'.$navtype.'-selected') : '').'"'.
					(isset($navlink['target']) ? (' target="'.$navlink['target'].'"') : '').'>'.$navlink['label'].
					'</a>'.
					(strlen(@$navlink['note']) ? (' ('.$navlink['note'].')') : '')
				);
			else
				$this->output($navlink['label']);
		}
		
		function logged_in()
		{
			$this->output_split(@$this->content['loggedin'], 'qa-logged-in', 'div');
		}
		
		function header_clear()
		{
			$this->output(
				'<div class="qa-header-clear">',
				'</div>'
			);
		}
		
		function sidepanel()
		{
			$this->output('<div class="qa-sidepanel">');
			$this->sidebar();
			$this->nav('cat');
			$this->output_raw(@$this->content['sidepanel']);
			$this->feed();
			$this->output('</div>', '');
		}
		
		function sidebar()
		{
			$sidebar=@$this->content['sidebar'];
			
			if (!empty($sidebar)) {
				$this->output('<div class="qa-sidebar">');
				$this->output_raw($sidebar);
				$this->output('</div>', '');
			}
		}
		
		function feed()
		{
			$feed=@$this->content['feed'];
			
			if (!empty($feed)) {
				$this->output('<div class="qa-feed">');
				$this->output('<a href="'.$feed['url'].'" class="qa-feed-link">'.@$feed['label'].'</a>');
				$this->output('</div>');
			}
		}
		
		function main()
		{
			$content=$this->content;

			$this->output('<div class="qa-main'.(@$this->content['hidden'] ? ' qa-main-hidden' : '').'">');
			
			//$this->page_title();
			$this->page_error();
			
			if (isset($content['main_form_tags']))
				$this->output('<form '.$content['main_form_tags'].' >');
				
			$this->main_parts($content);
		
			if (isset($content['main_form_tags']))
				$this->output('</form>');
				
			$this->page_links();
			$this->suggest_next();
			
			$this->output('</div> <!-- END qa-main -->', '');
		}
		
		function page_title()
		{
			$title=@$this->content['title'];
			
			if (strlen($title))
				$this->output('<h1>'.$title.'</h1>');
		}
		
		function page_error()
		{
			$error=@$this->content['error'];
			
			if (strlen($error))
				$this->output(
					'<div class="qa-error">',
					$error,
					'</div>'
				);
		}
		
		function main_parts($content)
		{
			foreach ($content as $key => $part) {
				if (strpos($key, 'custom')===0)
					$this->output_raw($part);

				elseif (strpos($key, 'form')===0)
					$this->form($part);
					
				elseif (strpos($key, 'q_list')===0)
					$this->q_list_and_form($part);

				elseif (strpos($key, 'q_view')===0)
					$this->q_view($part);
					
				elseif (strpos($key, 'a_list')===0)
					$this->a_list($part);
					
				elseif (strpos($key, 'ranking')===0)
					$this->ranking($part);
			}
		}
		
		function footer()
		{
			$this->output('<div class="qa-footer">');
			
			$this->custom_footer();
			//$this->nav('footer');
			$this->attribution();
			$this->footer_clear();
			
			$this->output('</div> <!-- END qa-footer -->', '');
		}
		
		function attribution()
		{
			// Hi there. I'd really appreciate you displaying this link on your Q2A site. Thank you - Gideon
				
			$this->output(
				'<div class="qa-attribution">',
				'Folosim <a href="http://www.question2answer.org/">Question2Answer</a>',
				' și iconițele <a href="http://somerandomdude.com/projects/iconic/">Iconic</a>.',
				'</div>'
			);
			$this->output('<script type="text/javascript" src="'.$this->rooturl.'js/ubuntu.js?0.1"></script>');
		}
		
		function footer_clear()
		{
			$this->output(
				'<div class="qa-footer-clear">',
				'</div>'
			);
		}

		function section($title)
		{
			if (!empty($title))
				$this->output('<h2>'.$title.'</h2>');
		}
		
		function form($form)
		{
			if (!empty($form)) {
				$this->section(@$form['title']);
				
				if (isset($form['tags']))
					$this->output('<form '.$form['tags'].'>');
				
				$this->form_body($form);
	
				if (isset($form['tags']))
					$this->output('</form>');
			}
		}
		
		function form_columns($form)
		{
			if (isset($form['ok']) || !empty($form['fields']) )
				$columns=($form['style']=='wide') ? 3 : 1;
			else
				$columns=0;
				
			return $columns;
		}
		
		function form_spacer($form, $columns)
		{
			$this->output(
				'<tr>',
				'<td colspan="'.$columns.'" class="qa-form-'.$form['style'].'-spacer">',
				'&nbsp;',
				'</td>',
				'</tr>'
			);
		}
		
		function form_body($form)
		{
			$columns=$this->form_columns($form);
			
			if ($columns)
				$this->output('<table class="qa-form-'.$form['style'].'-table">');
			
			$this->form_ok($form, $columns);
			$this->form_fields($form, $columns);
			$this->form_buttons($form, $columns);

			if ($columns)
				$this->output('</table>');

			$this->form_hidden($form);
		}
		
		function form_ok($form, $columns)
		{
			if (!empty($form['ok']))
				$this->output(
					'<tr>',
					'<td colspan="'.$columns.'" class="qa-form-'.$form['style'].'-ok">',
					$form['ok'],
					'</td>',
					'</tr>'
				);
		}
		
		function form_fields($form, $columns)
		{
			if (!empty($form['fields'])) {
				foreach ($form['fields'] as $field)
					if (@$field['type']=='blank')
						$this->form_spacer($form, $columns);
					else
						$this->form_field_rows($form, $columns, $field);
			}
		}
		
		function form_field_rows($form, $columns, $field)
		{
			$style=$form['style'];
			
			if (isset($field['style'])) { // field has different style to most of form
				$style=$field['style'];
				$colspan=$columns;
				$columns=($style=='wide') ? 3 : 1;
			} else
				$colspan=null;
			
			$prefixed=((@$field['type']=='checkbox') && ($columns==1) && !empty($field['label']));
			$suffixed=((@$field['type']=='select') && ($columns==1) && !empty($field['label']));
			$skipdata=@$field['tight'];
			$tworows=($columns==1) && (!empty($field['label'])) && (!$skipdata);
			
			if (($columns==1) && isset($field['id']))
				$this->output('<tbody id="'.$field['id'].'">', '<tr>');
			elseif (isset($field['id']))
				$this->output('<tr id="'.$field['id'].'">');
			else
				$this->output('<tr>');
			
			if (($columns>1) || !empty($field['label']))
				$this->form_label($field, $style, $columns, $prefixed, $suffixed, $colspan);
			
			if ($tworows)
				$this->output(
					'</tr>',
					'<tr>'
				);
			
			if (!$skipdata)
				$this->form_data($field, $style, $columns, !($prefixed||$suffixed), $colspan);
			
			$this->output('</tr>');
			
			if (($columns==1) && isset($field['id']))
				$this->output('</tbody>');
		}
		
		function form_label($field, $style, $columns, $prefixed, $suffixed, $colspan)
		{
			$this->output(
				'<td class="qa-form-'.$style.'-label"'.(isset($colspan) ? (' colspan="'.$colspan.'"') : '').'>'
			);
			
			if ($prefixed)
				$this->form_field($field, $style);
					
			$this->output(
				@$field['label']
			);
			
			if ($suffixed)
				$this->form_field($field, $style);
			
			$this->output('</td>');
		}
		
		function form_data($field, $style, $columns, $showfield, $colspan)
		{
			if ($showfield || (!empty($field['error'])) || (!empty($field['note']))) {
				$this->output(
					'<td class="qa-form-'.$style.'-data"'.(isset($colspan) ? (' colspan="'.$colspan.'"') : '').'>'
				);
							
				if ($showfield)
					$this->form_field($field, $style);
	
				if (!empty($field['error']))
					$this->form_error($field, $style, $columns);
				
				elseif (!empty($field['note']))
					$this->form_note($field, $style, $columns);
				
				$this->output('</td>');
			}
		}
		
		function form_field($field, $style)
		{
			$this->form_prefix($field, $style);
			
			switch (@$field['type']) {
				case 'checkbox':
					$this->form_checkbox($field, $style);
					break;
				
				case 'static':
					$this->form_static($field, $style);
					break;
				
				case 'password':
					$this->form_password($field, $style);
					break;
				
				case 'number':
					$this->form_number($field, $style);
					break;
				
				case 'select':
					$this->form_select($field, $style);
					break;
					
				case 'select-radio':
					$this->form_select_radio($field, $style);
					break;
					
				case 'image':
					$this->form_image($field, $style);
					break;
				
				case 'custom':
					echo @$field['html'];
					break;
				
				default:
					if ((@$field['type']=='textarea') || (@$field['rows']>1))
						$this->form_text_multi_row($field, $style);
					else
						$this->form_text_single_row($field, $style);
					break;
			}
		}
		
		function form_buttons($form, $columns)
		{
			if (!empty($form['buttons'])) {
				$style=$form['style'];
				
				if ($columns)
					$this->output(
						'<tr>',
						'<td colspan="'.$columns.'" class="qa-form-'.$style.'-buttons">'
					);

				foreach ($form['buttons'] as $key => $button) {
					$this->form_button_data($button, $key, $style);
					$this->form_button_note($button, $style);
				}
	
				if ($columns)
					$this->output(
						'</td>',
						'</tr>'
					);
			}
		}
		
		function form_button_data($button, $key, $style)
		{
			$baseclass='qa-form-'.$style.'-button qa-form-'.$style.'-button-'.$key;
			$hoverclass='qa-form-'.$style.'-hover qa-form-'.$style.'-hover-'.$key;
			
			$this->output('<input '.@$button['tags'].' value="'.@$button['label'].'" TITLE="'.@$button['popup'].'" type="submit" class="'.$baseclass.'" onmouseover="this.className=\''.$hoverclass.'\';" onmouseout="this.className=\''.$baseclass.'\';"/>');
		}
		
		function form_button_note($button, $style)
		{
			if (!empty($button['note']))
				$this->output(
					'<span class="qa-form-'.$style.'-note">',
					$button['note'],
					'</span>',
					'<br/>'
				);
		}
		
		function form_hidden($form)
		{
			if (!empty($form['hidden']))
				foreach ($form['hidden'] as $name => $value)
					$this->output('<input type="hidden" NAME="'.$name.'" value="'.$value.'"/>');
		}
		
		function form_prefix($field, $style)
		{
			if (!empty($field['prefix']))
				$this->output('<span class="qa-form-'.$style.'-prefix">'.$field['prefix'].'</span>');
		}
		
		function form_checkbox($field, $style)
		{
			$this->output('<input '.@$field['tags'].' type="checkbox" value="1"'.(@$field['value'] ? ' checked' : '').' class="qa-form-'.$style.'-checkbox"/>');
		}
		
		function form_static($field, $style)
		{
			$this->output('<span class="qa-form-'.$style.'-static">'.@$field['value'].'</span>');
		}
		
		function form_password($field, $style)
		{
			$this->output('<input '.@$field['tags'].' type="password" value="'.@$field['value'].'" class="qa-form-'.$style.'-text"/>');
		}
		
		function form_number($field, $style)
		{
			$this->output('<input '.@$field['tags'].' type="text" value="'.@$field['value'].'" class="qa-form-'.$style.'-number"/>');
		}
		
		function form_select($field, $style)
		{
			$this->output('<select '.@$field['tags'].' class="qa-form-'.$style.'-select">');
			
			foreach ($field['options'] as $tag => $value)
				$this->output('<option value="'.$tag.'"'.(($value==@$field['value']) ? ' selected' : '').'>'.$value.'</option>');
			
			$this->output('</select>');
		}
		
		function form_select_radio($field, $style)
		{
			$radios=0;
			
			foreach ($field['options'] as $tag => $value) {
				if ($radios++)
					$this->output('<br/>');
					
				$this->output('<input '.@$field['tags'].' type="radio" value="'.$tag.'"'.(($value==@$field['value']) ? ' checked' : '').' class="qa-form-'.$style.'-radio"/> '.$value);
			}
		}
		
		function form_image($field, $style)
		{
			$this->output('<div class="qa-form-'.$style.'-image">'.@$field['html'].'</div>');
		}
		
		function form_text_single_row($field, $style)
		{
			$this->output('<input '.@$field['tags'].' type="text" value="'.@$field['value'].'" class="qa-form-'.$style.'-text"/>');
		}
		
		function form_text_multi_row($field, $style)
		{
			$this->output('<textarea '.@$field['tags'].' rows="'.(int)$field['rows'].'" cols="40" class="qa-form-'.$style.'-text">'.@$field['value'].'</textarea>');
		}
		
		function form_error($field, $style, $columns)
		{
			$tag=($columns>1) ? 'span' : 'div';
			
			$this->output('<'.$tag.' class="qa-form-'.$style.'-error">'.$field['error'].'</'.$tag.'>');
		}
		
		function form_note($field, $style, $columns)
		{
			$tag=($columns>1) ? 'span' : 'div';
			
			$this->output('<'.$tag.' class="qa-form-'.$style.'-note">'.$field['note'].'</'.$tag.'>');
		}
		
		function ranking($ranking)
		{
			$this->section(@$ranking['title']);
			
			$class=(@$ranking['type']=='users') ? 'qa-top-users' : 'qa-top-tags';
			
			$rows=min($ranking['rows'], count($ranking['items']));
			
			if ($rows>0) {
				$this->output('<table class="'.$class.'-table">');
			
				$columns=ceil(count($ranking['items'])/$rows);
				
				for ($row=0; $row<$rows; $row++) {
					$this->output('<tr>');
		
					for ($column=0; $column<$columns; $column++)
						$this->ranking_item(@$ranking['items'][$column*$rows+$row], $class, $column>0);
		
					$this->output('</tr>');
				}
			
				$this->output('</table>');
			}
		}
		
		function ranking_item($item, $class, $spacer)
		{
			if (empty($item)) {
				if ($spacer)
					$this->ranking_spacer($class);

				$this->ranking_spacer($class);
				$this->ranking_spacer($class);
			
			} else {
				if ($spacer)
					$this->ranking_spacer($class);
				
				if (isset($item['count']))
					$this->ranking_count($item, $class);
					
				$this->ranking_label($item, $class);
					
				if (isset($item['score']))
					$this->ranking_score($item, $class);
			}
		}
		
		function ranking_spacer($class)
		{
			$this->output('<td class="'.$class.'-spacer">&nbsp;</td>');
		}
		
		function ranking_count($item, $class)
		{
			$this->output('<td class="'.$class.'-count">'.$item['count'].' &#215;'.'</td>');
		}
		
		function ranking_label($item, $class)
		{
			$this->output('<td class="'.$class.'-label">'.$item['label'].'</td>');
		}
		
		function ranking_score($item, $class)
		{
			$this->output('<td class="'.$class.'-score">'.$item['score'].'</td>');
		}
		
		function q_list_and_form($q_list)
		{
			if (!empty($q_list)) {
				$this->section(@$q_list['title']);
	
				if (!empty($q_list['form']))
					$this->output('<form '.$q_list['form']['tags'].'>');
				
				$this->q_list($q_list);
				
				if (!empty($q_list['form'])) {
					unset($q_list['form']['tags']); // we already output the tags before the qs
					$this->q_list_form($q_list);
					$this->output('</form>');
				}
			}
		}
		
		function q_list_form($q_list)
		{
			if (!empty($q_list['form'])) {
				$this->output('<div class="qa-q-list-form">');
				$this->form($q_list['form']);
				$this->output('</div>');
			}
		}
		
		function q_list($q_list)
		{
			$this->output('<div class="qa-q-list">', '');
			
			foreach ($q_list['qs'] as $question)
				$this->q_list_item($question);

			$this->output('</div> <!-- END qa-q-list -->', '');
		}
		
		function q_list_item($question)
		{
			$this->output('<div class="qa-q-list-item '.@$question['classes'].'" '.@$question['tags'].'>');

			$this->q_item_stats($question);
			$this->q_item_main($question);
			$this->q_item_clear();

			$this->output('</div> <!-- END qa-q-list-item -->', '');
		}
		
		function q_item_stats($question)
		{
			$this->output('<div class="qa-q-item-stats">');
			
			$this->voting($question);
			$this->a_count($question);

			$this->output('</div>');
		}
		
		function q_item_main($question)
		{
			$this->output('<div class="qa-q-item-main">');
			
			$this->q_item_title($question);
			$this->post_avatar($question, 'qa-q-item');
			$this->post_meta($question, 'qa-q-item');
			$this->post_tags($question, 'qa-q-item');
			
			$this->output('</div>');
		}
		
		function q_item_clear()
		{
			$this->output(
				'<div class="qa-q-item-clear">',
				'</div>'
			);
		}
		
		function q_item_title($question)
		{
			$this->output(
				'<div class="qa-q-item-title">',
				'<a href="'.$question['url'].'">'.$question['title'].'</a>',
				'</div>'
			);
		}
		
		function voting($post)
		{
			if (isset($post['vote_view'])) {
				$this->output('<div class="qa-voting '.(($post['vote_view']=='updown') ? 'qa-voting-updown' : 'qa-voting-net').'" '.@$post['vote_tags'].' >');
				$this->voting_inner_html($post);
				$this->output('</div>');
			}
		}
		
		function voting_inner_html($post)
		{
			$this->vote_buttons($post);
			$this->vote_count($post);
			$this->vote_clear();
		}
		
		function vote_buttons($post)
		{
			$this->output('<div class="qa-vote-buttons '.(($post['vote_view']=='updown') ? 'qa-vote-buttons-updown' : 'qa-vote-buttons-net').'">');

			switch (@$post['vote_state'])
			{
				case 'voted_up':
					$this->post_hover_button($post, 'vote_up_tags', '+', 'qa-vote-one-button qa-voted-up');
					break;
					
				case 'voted_up_disabled':
					$this->post_disabled_button($post, 'vote_up_tags', '+', 'qa-vote-one-button qa-vote-up');
					break;
					
				case 'voted_down':
					$this->post_hover_button($post, 'vote_down_tags', '&ndash;', 'qa-vote-one-button qa-voted-down');
					break;
					
				case 'voted_down_disabled':
					$this->post_disabled_button($post, 'vote_down_tags', '&ndash;', 'qa-vote-one-button qa-vote-down');
					break;
					
				case 'enabled':
					$this->post_hover_button($post, 'vote_up_tags', '+', 'qa-vote-first-button qa-vote-up');
					$this->post_hover_button($post, 'vote_down_tags', '&ndash;', 'qa-vote-second-button qa-vote-down');
					break;

				default:
					$this->post_disabled_button($post, 'vote_up_tags', '', 'qa-vote-first-button qa-vote-up');
					$this->post_disabled_button($post, 'vote_down_tags', '', 'qa-vote-second-button qa-vote-down');
					break;
			}

			$this->output('</div>');
		}
		
		function vote_count($post)
		{
			// You can also use $post['upvotes_raw'], $post['downvotes_raw'], $post['netvotes_raw'] to get
			// raw integer vote counts, for graphing or showing in other non-textual ways
			
			$this->output('<div class="qa-vote-count '.(($post['vote_view']=='updown') ? 'qa-vote-count-updown' : 'qa-vote-count-net').'">');

			if ($post['vote_view']=='updown') {
				$this->output_split($post['upvotes_view'], 'qa-upvote-count');
				$this->output_split($post['downvotes_view'], 'qa-downvote-count');
			
			} else
				$this->output_split($post['netvotes_view'], 'qa-netvote-count');

			$this->output('</div>');
		}
		
		function vote_clear()
		{
			$this->output(
				'<div class="qa-vote-clear">',
				'</div>'
			);
		}
		
		function a_count($post)
		{
			// You can also use $post['answers_raw'] to get a raw integer count of answers
			
			$this->output_split(@$post['answers'], 'qa-a-count', 'span', 'span',
				@$post['answer_selected'] ? 'qa-a-count-selected' : null);
		}
		
		function avatar($post, $class)
		{
			if (isset($post['avatar']))
				$this->output('<span class="'.$class.'-avatar">', $post['avatar'], '</span>');
		}
		
		function a_selection($post)
		{
			$this->output('<div class="qa-a-selection">');
			
			if (isset($post['select_tags']))
				$this->post_hover_button($post, 'select_tags', '', 'qa-a-select');
			elseif (isset($post['unselect_tags']))
				$this->post_hover_button($post, 'unselect_tags', '', 'qa-a-unselect');
			elseif ($post['selected'])
				$this->output('<div class="qa-a-selected">&nbsp;</div>');
			
			if (isset($post['select_text']))
				$this->output('<div class="qa-a-selected-text">'.@$post['select_text'].'</div>');
			
			$this->output('</div>');
		}
		
		function post_hover_button($post, $element, $value, $class)
		{
			if (isset($post[$element]))
				$this->output('<input '.$post[$element].' type="submit" value="'.$value.'" class="'.$class.
					'-button" onmouseover="this.className=\''.$class.'-hover\';" onmouseout="this.className=\''.$class.'-button\';"/> ');
		}
		
		function post_disabled_button($post, $element, $value, $class)
		{
			if (isset($post[$element]))
				$this->output('<input '.$post[$element].' type="submit" value="'.$value.'" class="'.$class.'-disabled" disabled="disabled"/> ');
		}
		
		function post_avatar($post, $class, $prefix=null)
		{
			if (isset($post['avatar'])) {
				if (isset($prefix))
					$this->output($prefix);

				$this->output('<span class="'.$class.'-avatar">', $post['avatar'], '</span>');
			}
		}
		
		function post_meta($post, $class, $prefix=null, $separator='<br/>')
		{
			$this->output('<span class="'.$class.'-meta">');
			
			if (isset($prefix))
				$this->output($prefix);
			
			$order=explode('^', @$post['meta_order']);
			
			foreach ($order as $element)
				switch ($element) {
					case 'what':
						$this->post_meta_what($post, $class);
						break;
						
					case 'when':
						$this->post_meta_when($post, $class);
						break;
						
					case 'where':
						$this->post_meta_where($post, $class);
						break;
						
					case 'who':
						$this->post_meta_who($post, $class);
						break;
				}
			
			if (!empty($post['when_2'])) {
				$this->output($separator);
				
				foreach ($order as $element)
					switch ($element) {
						case 'when':
							$this->output_split($post['when_2'], $class.'-when');
							break;
						
						case 'who':
							$this->output_split(@$post['who_2'], $class.'-who');
							break;
					}
			}
			
			$this->output('</span>');
		}
		
		function post_meta_what($post, $class)
		{
			if (isset($post['what'])) {
				if (isset($post['what_url']))
					$this->output('<a href="'.$post['what_url'].'" class="'.$class.'-what">'.$post['what'].'</a>');
				else
					$this->output('<span class="'.$class.'-what">'.$post['what'].'</span>');
			}
		}
		
		function post_meta_when($post, $class)
		{
			$this->output_split(@$post['when'], $class.'-when');
		}
		
		function post_meta_where($post, $class)
		{
			$this->output_split(@$post['where'], $class.'-where');
		}
		
		function post_meta_who($post, $class)
		{
			if (isset($post['who'])) {
				$this->output('<span class="'.$class.'-who">');
				
				if (strlen(@$post['who']['prefix']))
					$this->output('<span class="'.$class.'-who-pad">'.$post['who']['prefix'].'</span>');
				
				if (isset($post['who']['data']))
					$this->output('<span class="'.$class.'-who-data">'.$post['who']['data'].'</span>');
				
				if (isset($post['who']['title']))
					$this->output('<span class="'.$class.'-who-title">'.$post['who']['title'].'</span>');
					
				// You can also use $post['level'] to get the author's privilege level (as a string)
	
				if (isset($post['who']['points'])) {
					$post['who']['points']['prefix']='('.$post['who']['points']['prefix'];
					$post['who']['points']['suffix'].=')';
					$this->output_split($post['who']['points'], $class.'-who-points');
				}
				
				if (strlen(@$post['who']['suffix']))
					$this->output('<span class="'.$class.'-who-pad">'.$post['who']['suffix'].'</span>');
	
				$this->output('</span>');
			}
		}
		
		function post_tags($post, $class)
		{
			if (!empty($post['q_tags'])) {
				$this->output('<div class="'.$class.'-tags">');
				$this->post_tag_list($post, $class);
				$this->output('</div>');
			}
		}
		
		function post_tag_list($post, $class)
		{
			$this->output('<ul class="'.$class.'-tag-list">');
			
			foreach ($post['q_tags'] as $tag)
				$this->post_tag_item($tag, $class);
				
			$this->output('</ul>');
		}
		
		function post_tag_item($tag, $class)
		{
			$this->output('<li class="'.$class.'-tag-item">'.$tag.'</li>');
		}
	
		function page_links()
		{
			$page_links=@$this->content['page_links'];
			
			if (!empty($page_links)) {
				$this->output('<div class="qa-page-links">');
				
				$this->page_links_label(@$page_links['label']);
				$this->page_links_list(@$page_links['items']);
				$this->page_links_clear();
				
				$this->output('</div>');
			}
		}
		
		function page_links_label($label)
		{
			if (!empty($label))
				$this->output('<span class="qa-page-links-label">'.$label.'</span>');
		}
		
		function page_links_list($page_items)
		{
			if (!empty($page_items)) {
				$this->output('<ul class="qa-page-links-list">');
				
				foreach ($page_items as $page_link) {
					$this->page_links_item($page_link);
					
					if ($page_link['ellipsis'])
						$this->page_links_item(array('type' => 'ellipsis'));
				}
				
				$this->output('</ul>');
			}
		}
		
		function page_links_item($page_link)
		{
			$this->output('<li class="qa-page-links-item">');
			$this->page_link_content($page_link);
			$this->output('</li>');
		}
		
		function page_link_content($page_link)
		{
			$label=@$page_link['label'];
			$url=@$page_link['url'];
			
			switch ($page_link['type']) {
				case 'this':
					$this->output('<span class="qa-page-selected">'.$label.'</span>');
					break;
				
				case 'prev':
					$this->output('<a href="'.$url.'" class="qa-page-prev">&laquo; '.$label.'</a>');
					break;
				
				case 'next':
					$this->output('<a href="'.$url.'" class="qa-page-next">'.$label.' &raquo;</a>');
					break;
				
				case 'ellipsis':
					$this->output('<span class="qa-page-ellipsis">...</span>');
					break;
				
				default:
					$this->output('<a href="'.$url.'" class="qa-page-link">'.$label.'</a>');
					break;
			}
		}
		
		function page_links_clear()
		{
			$this->output(
				'<div class="qa-page-links-clear">',
				'</div>'
			);
		}

		function suggest_next()
		{
			$suggest=@$this->content['suggest_next'];
			
			if (!empty($suggest)) {
				$this->output('<div class="qa-suggest-next">');
				$this->output($suggest);
				$this->output('</div>');
			}
		}
		
		function q_view($q_view)
		{
			if (!empty($q_view)) {
				$this->output('<div class="qa-q-view'.(@$q_view['hidden'] ? ' qa-q-view-hidden' : '').' '.@$q_view['classes'].'" '.@$q_view['tags'].'>');
				
				$this->voting($q_view);
				$this->a_count($q_view);
				$this->page_title();
				$this->q_view_main($q_view);
				$this->q_view_clear();
				
				$this->output('</div> <!-- END qa-q-view -->', '');
			}
		}
		
		function q_view_main($q_view)
		{
			$this->output('<div class="qa-q-view-main">');

			$this->q_view_content($q_view);
			$this->post_tags($q_view, 'qa-q-view');
			$this->post_avatar($q_view, 'qa-q-view');
			$this->post_meta($q_view, 'qa-q-view');
			$this->q_view_follows($q_view);
			$this->q_view_buttons($q_view);
			$this->c_list(@$q_view['c_list'], 'qa-q-view');
			$this->form(@$q_view['a_form']);
			$this->c_list(@$q_view['a_form']['c_list'], 'qa-a-item');
			$this->form(@$q_view['c_form']);
			
			$this->output('</div> <!-- END qa-q-view-main -->');
		}
		
		function q_view_content($q_view)
		{
			if (!empty($q_view['content']))
				$this->output(
					'<div class="qa-q-view-content">',
					$q_view['content'],
					'</div>'
				);
		}
		
		function q_view_follows($q_view)
		{
			if (!empty($q_view['follows']))
				$this->output(
					'<div class="qa-q-view-follows">',
					$q_view['follows']['label'],
					'<a href="'.$q_view['follows']['url'].'" class="qa-q-view-follows-link">'.$q_view['follows']['title'].'</a>',
					'</div>'
				);
		}
		
		function q_view_buttons($q_view)
		{
			if (!empty($q_view['form'])) {
				$this->output('<div class="qa-q-view-buttons">');
				$this->form($q_view['form']);
				$this->output('</div>');
			}
		}
		
		function q_view_clear()
		{
			$this->output(
				'<div class="qa-q-view-clear">',
				'</div>'
			);
		}
		
		function a_list($a_list)
		{
			if (!empty($a_list)) {
				$this->section(@$a_list['title']);
				
				$this->output('<div class="qa-a-list">', '');
					
				foreach ($a_list['as'] as $a_item)
					$this->a_list_item($a_item);
				
				$this->output('</div> <!-- END qa-a-list -->', '');
			}
		}
		
		function a_list_item($a_item)
		{
			$extraclass=@$a_item['classes'].($a_item['hidden'] ? ' qa-a-list-item-hidden' : ($a_item['selected'] ? ' qa-a-list-item-selected' : ''));
			
			$this->output('<div class="qa-a-list-item '.$extraclass.'" '.@$a_item['tags'].'>');
			
			$this->voting($a_item);
			$this->a_item_main($a_item);
			$this->a_item_clear();

			$this->output('</div> <!-- END qa-a-list-item -->', '');
		}
		
		function a_item_main($a_item)
		{
			$this->output('<div class="qa-a-item-main">');
			
			if ($a_item['hidden'])
				$this->output('<div class="qa-a-item-hidden">');
			elseif ($a_item['selected'])
				$this->output('<div class="qa-a-item-selected">');

			$this->a_selection($a_item);
			$this->a_item_content($a_item);
			$this->post_avatar($a_item, 'qa-a-item');
			$this->post_meta($a_item, 'qa-a-item');
			$this->a_item_clear();
			
			if ($a_item['hidden'] || $a_item['selected'])
				$this->output('</div>');
			
			$this->a_item_buttons($a_item);
			$this->c_list(@$a_item['c_list'], 'qa-a-item');
			$this->form(@$a_item['c_form']);

			$this->output('</div> <!-- END qa-a-item-main -->');
		}
		
		function a_item_clear()
		{
			$this->output(
				'<div class="qa-a-item-clear">',
				'</div>'
			);
		}
		
		function a_item_content($a_item)
		{
			$this->output(
				'<div class="qa-a-item-content">',
				$a_item['content'],
				'</div>'
			);
		}
		
		function a_item_buttons($a_item)
		{
			if (!empty($a_item['form'])) {
				$this->output('<div class="qa-a-item-buttons">');
				$this->form($a_item['form']);
				$this->output('</div>');
			}
		}
		
		function c_list($c_list, $class)
		{
			if (!empty($c_list)) {
				$this->output('', '<div class="'.$class.'-c-list">');
					
				foreach ($c_list as $c_item)
					$this->c_list_item($c_item);
				
				$this->output('</div> <!-- END qa-c-list -->', '');
			}
		}
		
		function c_list_item($c_item)
		{
			$extraclass=@$c_item['classes'].($c_item['hidden'] ? ' qa-c-item-hidden' : '');
			
			$this->output('<div class="qa-c-list-item '.$extraclass.'" '.@$c_item['tags'].'>');
			$this->c_item_main($c_item);
			$this->c_item_clear();
			$this->output('</div> <!-- END qa-c-item -->');
		}
		
		function c_item_main($c_item)
		{
			if (isset($c_item['url']))
				$this->c_item_link($c_item);
			else
				$this->c_item_content($c_item);
			
			$this->output('<div class="qa-c-item-footer">');
			$this->post_avatar($c_item, 'qa-c-item');
			$this->post_meta($c_item, 'qa-c-item', null, '&mdash;');
			$this->c_item_buttons($c_item);
			$this->output('</div>');
		}
		
		function c_item_link($c_item)
		{
			$this->output(
				'<a href="'.$c_item['url'].'" class="qa-c-item-link">'.$c_item['title'].'</a>'
			);
		}
		
		function c_item_content($c_item)
		{
			$this->output(
				'<span class="qa-c-item-content">',
				$c_item['content'],
				'</span>'
			);
		}
		
		function c_item_buttons($c_item)
		{
			if (!empty($c_item['form'])) {
				$this->output('<div class="qa-c-item-buttons">');
				$this->form($c_item['form']);
				$this->output('</div>');
			}
		}
		
		function c_item_clear()
		{
			$this->output(
				'<div class="qa-c-item-clear">',
				'</div>'
			);
		}
		
		function custom_footer() {
			ob_start();
				include( 'custom_footer.php' );
			$custom_footer = ob_get_clean();
			
			$this->output(
				'<div class="qa-custom-footer">',
				$custom_footer,
				'</div>'
			);
		}
	}
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
