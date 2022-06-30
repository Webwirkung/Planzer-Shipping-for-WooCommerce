<style>
  .delivery-label-text {
    width: 100%;
    font-size: 16px;
    text-align: center;
  }
  .mb-30 {
    margin-bottom: 30px;
  }
  table {
    margin-left: auto;
    margin-right: auto;
    border-spacing: 0 0;
  }
  td {
    font-size: 18px;
    text-align: center;
  }
  .bold {
    font-weight: bold;
  }
</style>

<div>
  <div class="delivery-label-text mb-30 bold">
    <?php echo esc_html($data['package_number']); ?>
  </div>
  <table width="455px" class="mb-30">
    <tr>
      <td style="width:100px;font-size: 16px;" text-rotate="90" class="bold">
        <?php echo esc_html($data['order']->get_shipping_first_name() ?: $data['order']->get_billing_first_name()); ?> <?php echo esc_html($data['order']->get_shipping_last_name() ?: $data['order']->get_billing_last_name()); ?>
      <td>
      <td style="width:255px;">
        <img width="255" height="255" src="<?php echo esc_url($data['qr_url']); ?>">
      </td>
      <td style="width: 100px; font-size: 16px;" text-rotate="-90" class="bold">
       <?php echo esc_html($data['contact_name']); ?>
      </td>
    </tr>
  </table>
  <div class="delivery-label-text bold">
    <?php echo esc_html($data['sequence_number']); ?>
  </div>
</div>
