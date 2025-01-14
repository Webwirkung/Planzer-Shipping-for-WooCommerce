<?php

namespace Planzer\WooCommerce\Services;

class ExclusionService {
	public function getExclusionReason(\WC_Order $order): ?string {
		if ($this->hasExcludedProducts($order)) {
			return __('Products in the order are excluded from Planzer delivery', 'planzer');
		}
		
		if ($this->hasExcludedShippingMethod($order)) {
			return __('The order shipping method is excluded from Planzer delivery', 'planzer');
		}
		
		if ($this->hasExcludedShippingClass($order)) {
			return __('The order contains products with excluded shipping classes', 'planzer');
		}
		
		return null;
	}

	public function isOrderExcluded(\WC_Order $order): bool {
		return $this->getExclusionReason($order) !== null;
	}

	private function hasExcludedProducts(\WC_Order $order): bool {
		$excluded_ids = $this->getExcludedProductIds();
		if (in_array('none', $excluded_ids)) {
			return false;
		}

		$order_items_id = array_map(
			fn($item): int => $item->get_product_id(), 
			$order->get_items()
		);
		
		return empty(array_diff($order_items_id, $excluded_ids));
	}

	private function hasExcludedShippingMethod(\WC_Order $order): bool {
		$excluded_methods = $this->getExcludedShippingMethods();
		if (in_array('none', $excluded_methods)) {
			return false;
		}

		return in_array($order->get_shipping_method(), $excluded_methods);
	}

	private function hasExcludedShippingClass(\WC_Order $order): bool {
		$excluded_classes = $this->getExcludedShippingClasses();
		if (in_array('none', $excluded_classes)) {
			return false;
		}
	
		$shipping_items = $order->get_items('shipping');
		if (empty($shipping_items)) {
			return false;
		}
	
		foreach ($shipping_items as $shipping_item) {
			$instance_id = $shipping_item->get_instance_id();
			$shipping_method = \WC_Shipping_Zones::get_shipping_method($instance_id);
			
			if ($shipping_method && $shipping_method instanceof \WC_Shipping_Flat_Rate) {
				$type = $shipping_method->get_option('type');
				
				$shipping_classes = [];
				foreach ($order->get_items() as $item) {
					$product = $item->get_product();
					if (!$product) continue;
					
					$class_id = $product->get_shipping_class_id();
					if ($class_id) {
						$shipping_classes[] = $class_id;
					}
				}
				
				if (empty($shipping_classes)) {
					continue;
				}
	
				$unique_classes = array_unique($shipping_classes);
	
				if ($type === 'class') {
					$non_excluded_classes = array_diff($unique_classes, $excluded_classes);
					if (empty($non_excluded_classes)) {
						return true;
					}
				}
				else if ($type === 'order') {
					if (count($unique_classes) === 1) {
						return in_array(reset($unique_classes), $excluded_classes);
					}
					
					$highest_cost = -1;
					$highest_class_id = null;
					
					foreach ($unique_classes as $class_id) {
						$class_cost_setting = $shipping_method->get_option('class_cost_' . $class_id, 0);
						$cost = is_numeric($class_cost_setting) ? floatval($class_cost_setting) : 0;
						
						if ($cost > $highest_cost) {
							$highest_cost = $cost;
							$highest_class_id = $class_id;
						}
					}
					
					if ($highest_class_id && in_array($highest_class_id, $excluded_classes)) {
						return true;
					}
				}
			}
		}
	
		return false;
	}

	private function getExcludedProductIds(): array {
		$excluded_ids = get_option('planzer_other_excluded_products', []);
		return ('none' === $excluded_ids || false === $excluded_ids) 
			? ['none'] 
			: $excluded_ids;
	}

	private function getExcludedShippingMethods(): array {
		$excluded_methods = get_option('planzer_other_excluded_shipping', []);
		return ('none' === $excluded_methods || false === $excluded_methods)
			? ['none']
			: $excluded_methods;
	}

	private function getExcludedShippingClasses(): array {
		$excluded_classes = get_option('planzer_other_excluded_shipping_classes', []);
		return ('none' === $excluded_classes || false === $excluded_classes)
			? ['none']
			: $excluded_classes;
	}
}