<?php
	class pagination_module
	{

		public function __construct()
		{	
			$this->opus =& opus::$instance;
		}

		public function make_links($total_items, $items_per_page = FALSE)
		{
			$items_per_page = ($items_per_page !== FALSE) ? $items_per_page : $this->opus->config->pagination->items_per_page;
			$current_page = (! empty($this->opus->urlargs->get_parameter('page'))) ? $this->opus->urlargs->get_parameter('page') : 1;
			$pages = ceil($total_items / $items_per_page);

			$html = '';

			if ($pages < 2)
				return $html;

			$html .= ' <a ' . (($current_page == 2) ? 'rel="prev"' : '')  . ' href="' . $this->opus->url('movies' . $this->opus->urlargs->get_url(array('page' => 1))) . '">' . $this->opus->config->pagination->start_string . '</a>';

			if ($current_page > 1)
				$html .= ' <a rel="prev" href="' . $this->opus->url('movies' . $this->opus->urlargs->get_url(array('page' => ($current_page - 1)))) . '">' . $this->opus->config->pagination->prev_string . '</a>';

			for ($x = 1; $x <= $pages; $x++)
			{
				$html .= ' <a ';
				$html .= ($x == $current_page - 1) ? 'rel="prev"' : '';
				$html .= ($x == $current_page + 1) ? 'rel="next"' : '';
				$html .= ' href="' . $this->opus->url('movies' . $this->opus->urlargs->get_url(array('page' => $x))) . '">';
				$html .= ($current_page == $x) ? '<strong>' . $x . '</strong>' : $x;
				$html .= '</a>';
			}

			if ($current_page < $pages)
				$html .= ' <a rel="next" href="' . $this->opus->url('movies' . $this->opus->urlargs->get_url(array('page' => ($current_page + 1)))) . '">' . $this->opus->config->pagination->next_string . '</a>';

			$html .= ' <a ' . (($current_page == $pages) ? 'rel="next"' : '')  . 'href="' . $this->opus->url('movies' . $this->opus->urlargs->get_url(array('page' => $pages))) . '">' . $this->opus->config->pagination->last_string . '</a>';

			return $html;
		}

	}
?>