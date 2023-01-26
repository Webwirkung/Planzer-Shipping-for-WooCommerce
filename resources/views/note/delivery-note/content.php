<style>
  .logo {
    padding-top: -20px;
    text-align: right;
  }
  .company-name {
    margin: 35px 0 5px;
    font-size: 10px;
    border-bottom: 1px solid #000000;
    padding-bottom: 5px;
    display:inline-block;
  }
  .customer {
    font-size: 16px;
    font-weight: normal;
    line-height: 20px;
    margin-bottom: 40px;
    float: left;
    width: 28%;
  }
  .delivery-note {
    font-size: 16px;
    font-weight: bold;
  }
  .delivery-note-table,
  .order-table {
    width: 100%;
    margin: 5px 0 25px;
    text-align: left;
    border-collapse: collapse;
    border-top: 1px solid brown;
    border-bottom: 1px solid brown;
  }

  .delivery-note-table td,
  .order-table td {
    font-size: 12px;
  }

  .dear-sir {
    font-size: 12px;
    line-height: 14px;
    margin-bottom: 25px;
  }

  .order-table .thead {
    font-size: 10px;
    text-align: left;
    margin-bottom: 5px;
  }
  .order-tr td{
    padding: 5px 0;
  }
  .customer-note,
  .thank-you {
    font-size: 10px;
    line-height: 12px;
    margin-bottom: 25px;
  }
  .package-number {
    text-align: right;
    width: 71%;
    font-size: 12px;
  }
</style>
<?php if (! empty($data['logo'])) : ?>
  <div class="logo">
    <img width="150" src="<?php echo esc_attr($data['logo']);?>">
  </div>
<?php endif; ?>
<div class="company-name">
  <?php echo esc_html($data['company']); ?><?php echo esc_html(empty($data['company_extra']) ? '' : (" {$data['company_extra']}")); ?>, <?php echo esc_html($data['company_address']->get_base_address()); ?>, <?php echo esc_html($data['company_address']->get_base_postcode()); ?> <?php echo esc_html($data['company_city']); ?>
</div>
<div style="clear: both;"></div>
<div class="customer">
  <?php echo esc_html($data['order']->get_shipping_first_name() ?: $data['order']->get_billing_first_name()); ?> <?php echo esc_html($data['order']->get_shipping_last_name() ?: $data['order']->get_billing_last_name()); ?> <br/>
  <?php echo esc_html(($data['order']->get_billing_company() === $data['order']->get_shipping_company()) ? $data['order']->get_billing_company() : $data['order']->get_shipping_company()); ?><br/>
  <?php echo esc_html($data['order']->get_shipping_address_1() ?: $data['order']->get_billing_address_1()); ?> <?php echo esc_html($data['order']->get_shipping_address_2() ?: $data['order']->get_billing_address_2()); ?> <br/>
  <?php echo esc_html($data['order']->get_shipping_postcode() ?: $data['order']->get_billing_postcode()); ?> <?php echo esc_html($data['order']->get_shipping_city() ?: $data['order']->get_billing_city()); ?><br/>
  <?php echo esc_html($data['country']); ?>
</div>
<div class="package-number">
  <?php echo __('Package number', 'planzer'); ?>: <?php echo esc_html($data['package_number']); ?><br/><br/>
  <img class="qr-code" width="160" src="<?php echo esc_url($data['qr_url']); ?>"/>
</div>
<div style="clear: both;"></div>
<br/>
<br/>
<br/>
<div class="delivery-note">
  <?php echo __('Delivery note LS', 'planzer'); ?>-<?php echo esc_html($data['sequence_number']); ?> / <?php echo __('Order number', 'planzer'); ?> <?php echo esc_html($data['order']->get_id()); ?>
</div>
<table class="delivery-note-table">
  <tbody class="tbody">
    <tr>
      <td class='first-td'><?php echo __('Date', 'planzer'); ?>:</td>
      <td><?php echo esc_html($data['order']->get_date_created()->date('d.m.Y')); ?></td>
      <td><?php echo __('Customer number', 'planzer'); ?>:</td>
      <td><?php echo esc_html($data['order']->get_customer_id()); ?></td>
    </tr>
    <tr>
      <td><?php echo __('Your contact person', 'planzer'); ?>:</td>
      <td><?php echo esc_html($data['contact_name']); ?></td>
      <td></td>
      <td></td>
    </tr>
  </tbody>
</table>
<div class="dear-sir">
  <?php echo $data['salutation'] ?> <br><br>
</div>
<table class="order-table">
  <thead>
    <tr align="left">
      <th class='thead'><?php echo __('Item', 'planzer'); ?>.</th>
      <th class='thead'><?php echo __('Description', 'planzer'); ?></th>
      <th class='thead'><?php echo __('Unit', 'planzer', 'planzer'); ?></th>
      <th class='thead'><?php echo __('Quantity ordered', 'planzer'); ?></th>
      <th class='thead'><?php echo __('Crowd open', 'planzer'); ?></th>
      <th class='thead'><?php echo __('Quantity delivered', 'planzer'); ?></th>
    </tr>
  </thead>
  <tbody class="tbody">
  <?php
    $x = 1;
    foreach ($data['order']->get_items() as $item_id => $item) :
      $product = wc_get_product($item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id());
      $refundQuantity = $data['order']->get_qty_refunded_for_item($item->get_id());

      if (! empty($data['excluded_products_ids']) && in_array($item->get_product_id(), $data['excluded_products_ids'])) {
        continue;
      }

      if (0 !== $refundQuantity && (string) $data['order']->get_total_refunded_for_item($item->get_id()) === $item->get_total()) {
        continue;
      }

      $quantity = $item->get_quantity();

      if (0 !== $refundQuantity) {
        $quantity -= abs($refundQuantity);
      }

      ?>
        <tr valign="top" class="order-tr">
          <td><?php echo esc_html($x); ?></td>
          <td><?php echo esc_html($item->get_name()); ?><br><?php echo __('Product Code', 'planzer'); ?>: <?php echo esc_html($product->get_sku()); ?></td>
          <td></td>
          <td align="center"><?php echo esc_html($quantity); ?></td>
          <td align="center">0</td>
          <td align="center"><?php echo esc_html($quantity); ?></td>
        </tr>
      <?php
      $x++;
    endforeach;
  ?>
  </tbody>
</table>
<?php if (! empty($data['order']->get_customer_note())) : ?>
  <div class="customer-note">
    <?php echo __('Annotation:', 'planzer'); ?>
    <br>
    <?php echo $data['order']->get_customer_note(); ?>
  </div>
<?php endif; ?>
<div class="thank-you">
  <?php echo $data['signature']; ?>
</div>