<?php
namespace Opencart\Admin\Controller\Catalog;
/**
 * Class Product
 *
 * @package Opencart\Admin\Controller\Catalog
 */
class Consignee extends \Opencart\System\Engine\Controller {
	/**
	 * @return void
	 */
	public function index(): void {
		$this->load->language('catalog/consignee');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->get['filter_vendor'])) {
			$filter_vendor = $this->request->get['filter_vendor'];
		} else {
			$filter_vendor = '';
		}

		$url = '';

		if (isset($this->request->get['filter_vendor'])) {
			$url .= '&filter_vendor=' . urlencode(html_entity_decode($this->request->get['filter_vendor'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['add'] = $this->url->link('catalog/product.form', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['copy'] = $this->url->link('catalog/product.copy', 'user_token=' . $this->session->data['user_token']);
		$data['delete'] = $this->url->link('catalog/product.delete', 'user_token=' . $this->session->data['user_token']);

		$data['list'] = $this->getList();

		$data['filter_vendor'] = $filter_vendor;

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/consignee', $data));
	}

	/**
	 * @return void
	 */
	public function list(): void {
		$this->load->language('catalog/consignee');

		$this->response->setOutput($this->getList());
	}

	/**
	 * @return string
	 */
	protected function getList(): string {
		if (isset($this->request->get['filter_vendor'])) {
			$filter_vendor = $this->request->get['filter_vendor'];
		} else {
			$filter_vendor = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = (string)$this->request->get['sort'];
		} else {
			$sort = 'pd.vendor';
		}

		if (isset($this->request->get['order'])) {
			$order = (string)$this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_vendor'])) {
			$url .= '&filter_vendor=' . urlencode(html_entity_decode($this->request->get['filter_vendor'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['action'] = $this->url->link('catalog/product.list', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['products'] = [];

		$filter_data = [
			'filter_vendor'     => $filter_vendor,
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit'           => $this->config->get('config_pagination_admin')
		];

		$this->load->model('catalog/consignee');

		$this->load->model('tool/image');

		$product_total = $this->model_catalog_consignee->getTotalProducts($filter_data);

		$results = $this->model_catalog_consignee->getProducts($filter_data);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
				$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}

			$special = false;

			$product_specials = $this->model_catalog_consignee->getSpecials($result['consignee_id']);

			foreach ($product_specials as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
					$special = $this->currency->format($product_special['price'], $this->config->get('config_currency'));

					break;
				}
			}

			// $categories = $this->model_catalog_product->getCategoryByProductId($result['product_id']);

			// // Initialize an empty array to hold category paths
			// $category_paths = array();
			
			// // Loop through categories and build category paths
			// foreach ($categories as $category) {
			// 	$category_paths[] = $category['category_path'];
			// }
			
			// // Join category paths with a separator (e.g., comma)
			// $category_list = implode(', ', $category_paths);

			$data['products'][] = [
				'product_id' => $result['consignee_id'],
				'image'      => $image,
				'name' => $result['consignee_name'],
				'category'    => $result['name'],
				'tag' => $result['tag'],
				'meta_title' => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword' => $result['meta_keyword'],
				'status' => $result['status'],
				// 'categories' => $category_list, // Include category information
				'edit' => $this->url->link('catalog/consignee/form', 'user_token=' . $this->session->data['user_token'] . '&consignee_id=' . $result['consignee_id'] . $url)
			];
		}

		$url = '';

		if (isset($this->request->get['filter_vendor'])) {
			$url .= '&filter_vendor=' . urlencode(html_entity_decode($this->request->get['filter_vendor'], ENT_QUOTES, 'UTF-8'));
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		$data['sort_vendor'] = $this->url->link('catalog/consignee.list', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.vendor' . $url);

		$url = '';

		if (isset($this->request->get['filter_vendor'])) {
			$url .= '&filter_vendor=' . urlencode(html_entity_decode($this->request->get['filter_vendor'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $product_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link('catalog/product.list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($product_total - $this->config->get('config_pagination_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $product_total, ceil($product_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view('catalog/consignee_list', $data);
	}

	/**
	 * @return void
	 */

	/**
	 * @return void
	 */
	public function save(): void {
		$this->load->language('catalog/consignee');

		$json = [];

		if (!$this->user->hasPermission('modify', 'catalog/consignee')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		// foreach ($this->request->post['product_description'] as $language_id => $value) {
		// 	if ((oc_strlen(trim($value['name'])) < 1) || (oc_strlen($value['name']) > 255)) {
		// 		$json['error']['name_' . $language_id] = $this->language->get('error_name');
		// 	}
		// 	// Change: Remove meta field check
		// 	// if ((oc_strlen(trim($value['meta_title'])) < 1) || (oc_strlen($value['meta_title']) > 255)) {
		// 	// 	$json['error']['meta_title_' . $language_id] = $this->language->get('error_meta_title');
		// 	// }
		// }

		if ((oc_strlen($this->request->post['model']) < 1) || (oc_strlen($this->request->post['model']) > 64)) {
			$json['error']['model'] = $this->language->get('error_model');
		}

		$this->load->model('catalog/product');

		if ($this->request->post['master_id']) {
			$product_options = $this->model_catalog_product->getOptions($this->request->post['master_id']);

			foreach ($product_options as $product_option) {
				if (isset($this->request->post['override']['variant'][$product_option['product_option_id']]) && $product_option['required'] && empty($this->request->post['variant'][$product_option['product_option_id']])) {
					$json['error']['option_' . $product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
				}
			}
		}

		// if ($this->request->post['product_seo_url']) {
		// 	$this->load->model('design/seo_url');

		// 	foreach ($this->request->post['product_seo_url'] as $store_id => $language) {
		// 		foreach ($language as $language_id => $keyword) {
		// 			if ((oc_strlen(trim($keyword)) < 1) || (oc_strlen($keyword) > 64)) {
		// 				$json['error']['keyword_' . $store_id . '_' . $language_id] = $this->language->get('error_keyword');
		// 			}

		// 			if (preg_match('/[^a-zA-Z0-9\/_-]|[\p{Cyrillic}]+/u', $keyword)) {
		// 				$json['error']['keyword_' . $store_id . '_' . $language_id] = $this->language->get('error_keyword_character');
		// 			}

		// 			$seo_url_info = $this->model_design_seo_url->getSeoUrlByKeyword($keyword, $store_id);

		// 			if ($seo_url_info && ($seo_url_info['key'] != 'product_id' || !isset($this->request->post['product_id']) || $seo_url_info['value'] != (int)$this->request->post['product_id'])) {
		// 				$json['error']['keyword_' . $store_id . '_' . $language_id] = $this->language->get('error_keyword_exists');
		// 			}
		// 		}
		// 	}
		// }

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			if (!$this->request->post['product_id']) {
				if (!$this->request->post['master_id']) {
					// Normal product add
					$json['product_id'] = $this->model_catalog_product->addProduct($this->request->post);
				} else {
					// Variant product add
					$json['product_id'] = $this->model_catalog_product->addVariant($this->request->post['master_id'], $this->request->post);
				}
			} else {
				if (!$this->request->post['master_id']) {
					// Normal product edit
					$this->model_catalog_product->editProduct($this->request->post['product_id'], $this->request->post);
				} else {
					// Variant product edit
					$this->model_catalog_product->editVariant($this->request->post['master_id'], $this->request->post['product_id'], $this->request->post);
				}

				// Variant products edit if master product is edited
				$this->model_catalog_product->editVariants($this->request->post['product_id'], $this->request->post);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * @return void
	 */
	public function delete(): void {
		$this->load->language('catalog/consignee');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('catalog/product');

			foreach ($selected as $product_id) {
				$this->model_catalog_product->deleteProduct($product_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * @return void
	 */
	public function copy(): void {
		$this->load->language('catalog/consignee');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('catalog/product');

			foreach ($selected as $product_id) {
				$this->model_catalog_product->copyProduct($product_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * @return void
	 */
	public function report(): void {
		$this->load->language('catalog/consignee');

		$this->response->setOutput($this->getReport());
	}

	/**
	 * @return string
	 */
	public function getReport(): string {
		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->get['page']) && $this->request->get['route'] == 'catalog/product.report') {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$limit = 10;

		$data['reports'] = [];

		$this->load->model('catalog/product');
		$this->load->model('setting/store');

		$results = $this->model_catalog_product->getReports($product_id, ($page - 1) * $limit, $limit);

		foreach ($results as $result) {
			$store_info = $this->model_setting_store->getStore($result['store_id']);

			if ($store_info) {
				$store = $store_info['name'];
			} elseif (!$result['store_id']) {
				$store = $this->config->get('config_name');
			} else {
				$store = '';
			}

			$data['reports'][] = [
				'ip'         => $result['ip'],
				'store'      => $store,
				'country'    => $result['country'],
				'date_added' => date($this->language->get('datetime_format'), strtotime($result['date_added']))
			];
		}

		$report_total = $this->model_catalog_product->getTotalReports($product_id);

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $report_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('catalog/product.report', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $product_id . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($report_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($report_total - $limit)) ? $report_total : ((($page - 1) * $limit) + $limit), $report_total, ceil($report_total / $limit));

		return $this->load->view('catalog/product_report', $data);
	}

	/**
	 * @return void
	 */
	public function autocomplete(): void {
		$json = [];

		if (isset($this->request->get['filter_vendor'])) {
			$filter_vendor = $this->request->get['filter_vendor'];
		} else {
			$filter_vendor = '';
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = 5;
		}

		$filter_data = [
			'filter_vendor'  => $filter_vendor,
			'start'        => 0,
			'limit'        => $limit
		];

		$this->load->model('catalog/product');
		$this->load->model('catalog/option');
		$this->load->model('catalog/subscription_plan');
		$this->load->model('catalog/category');

		$results = $this->model_catalog_product->getProducts($filter_data);

		foreach ($results as $result) {

			$option_data = [];

			$product_options = $this->model_catalog_product->getOptions($result['product_id']);

			foreach ($product_options as $product_option) {
				$option_info = $this->model_catalog_option->getOption($product_option['option_id']);

				if ($option_info) {
					$product_option_value_data = [];

					foreach ($product_option['product_option_value'] as $product_option_value) {
						$option_value_info = $this->model_catalog_option->getValue($product_option_value['option_value_id']);

						if ($option_value_info) {
							$product_option_value_data[] = [
								'product_option_value_id' => $product_option_value['product_option_value_id'],
								'option_value_id'         => $product_option_value['option_value_id'],
								'name'                    => $option_value_info['name'],
								'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
								'price_prefix'            => $product_option_value['price_prefix']
							];
						}
					}

					$option_data[] = [
						'product_option_id'    => $product_option['product_option_id'],
						'product_option_value' => $product_option_value_data,
						'option_id'            => $product_option['option_id'],
						'name'                 => $option_info['name'],
						'type'                 => $option_info['type'],
						'value'                => $product_option['value'],
						'required'             => $product_option['required']
					];
				}
			}

			$subscription_data = [];

			$product_subscriptions = $this->model_catalog_product->getSubscriptions($result['product_id']);

			foreach ($product_subscriptions as $product_subscription) {
				$subscription_plan_info = $this->model_catalog_subscription_plan->getSubscriptionPlan($product_subscription['subscription_plan_id']);

				if ($subscription_plan_info) {
					$subscription_data[] = [
						'subscription_plan_id' => $subscription_plan_info['subscription_plan_id'],
						'name'                 => $subscription_plan_info['name']
					];
				}
			}

			$json[] = [
				'product_id'   => $result['product_id'],
				'name'         => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
				'model'        => $result['model'],
				'category'     => implode(', ', array_unique(explode(', ', $result['category']))), //added this to display the column
				'option'       => $option_data,
				'subscription' => $subscription_data,
				'price'        => $result['price']
			];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
