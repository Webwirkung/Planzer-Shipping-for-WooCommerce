<style>
  .logo {
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
  .deliviery-note {
    font-size: 16px;
    font-weight: bold;
  }
  .deliviery-note-table,
  .order-table {
    width: 100%;
    margin: 5px 0 25px;
    text-align: left;
    border-collapse: collapse;
    border-top: 1px solid brown;
    border-bottom: 1px solid brown;
  }

  .deliviery-note-table td,
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
    <img width="150" src="<?php echo $data['logo'];?>">
  </div>
<?php endif; ?>
<div class="company-name">
  <?php echo $data['company']; ?>, <?php echo $data['company_address']->get_base_address(); ?>, <?php echo $data['company_address']->get_base_postcode(); ?> <?php echo $data['country']; ?>
</div>
<div style="clear: both;"></div>
<div class="customer">
  <?php echo $data['order']->get_shipping_first_name() ?: $data['order']->get_billing_first_name(); ?> <?php echo $data['order']->get_shipping_last_name() ?: $data['order']->get_billing_last_name(); ?> <br/>
  <?php echo $data['order']->get_shipping_company() ?: $data['order']->get_billing_company(); ?><br/>
  <?php echo $data['order']->get_shipping_address_1() ?: $data['order']->get_billing_address_1(); ?> <?php echo $data['order']->get_shipping_address_2() ?: $data['order']->get_billing_address_2(); ?> <br/>
  <?php echo $data['order']->get_shipping_postcode() ?: $data['order']->get_billing_postcode(); ?> <?php echo $data['order']->get_shipping_city() ?: $data['order']->get_billing_city(); ?><br/>
  <?php echo $data['country']; ?>
</div>
<div class="package-number">
  <?php echo __('Package number', 'planzer'); ?>: <?php echo $data['package_number']; ?><br/>
  <img src="<?php echo $data['qr_url']; ?>"/>
</div>
<div style="clear: both;"></div>
<br/>
<br/>
<div class="deliviery-note">
  <?php echo __('Delivery note LS', 'planzer'); ?>-<?php echo $data['sequence_number']; ?>
</div>
<table class="deliviery-note-table">
  <tbody class="tbody">
    <tr>
      <td class='first-td'><?php echo __('Date', 'planzer'); ?>:</td>
      <td><?php echo $data['order']->get_date_created()->date('d.m.Y'); ?></td>
      <td><?php echo __('Customer number', 'planzer'); ?>:</td>
      <td><?php echo $data['order']->get_customer_id(); ?></td>
    </tr>
    <tr>
      <td><?php echo __('Your contact person', 'planzer'); ?>:</td>
      <td><?php echo $data['contact_name']; ?></td>
      <td></td>
      <td></td>
    </tr>
  </tbody>
</table>
<div class="dear-sir">
  <?php echo __('Ladies and gentlemen', 'planzer'); ?> <br><br>
  <?php echo __('Find attached', 'planzer'); ?>:
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
      $product = wc_get_product($item->get_product_id());

      if (in_array($item->get_product_id(), $data['excluded_products_ids'])) {
        continue;
      }
      ?>
        <tr valign="top" class="order-tr">
          <td><?php echo $x; ?></td>
          <td><?php echo $item->get_name(); ?><br><?php echo __('Product Code', 'planzer'); ?>: <?php echo $product->get_sku(); ?></td>
          <td></td>
          <td align="center"><?php echo $item->get_quantity(); ?></td>
          <td align="center">0</td>
          <td align="center"><?php echo $item->get_quantity(); ?></td>
        </tr>
      <?php
      $x++;
    endforeach;
  ?>
  </tbody>
</table>
<div class="thank-you">
  <?php echo __('Thank you very much for the order.', 'planzer'); ?><br><br>
  <?php echo __('Feel free to contact us if you have any questions.', 'planzer'); ?><br><br>
  <?php echo __('Kind regards', 'planzer'); ?><br>
  <?php echo $data['contact_name']; ?> <br>
  <?php echo $data['company']; ?>
</div>