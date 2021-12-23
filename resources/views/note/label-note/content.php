<style>
  .delivery-label-text {
    font-size: 18px;
  }
  .rotate-90 {
    position: absolute;
    rotate: 90;
  }
  .rotate-270 {
    transform: rotate(270deg);
  }
  td {
    padding: 15px;
  }
</style>

<table style="width:100%; height:100%;">
  <tr>
    <td align="center">
      <table>
        <tr align="center">
          <td colspan="3" class="delivery-label-text"><?php echo esc_html($data['package_number']); ?></td>
        </tr>
        <tr>
          <td class="delivery-label-text" text-rotate="90"><?php echo esc_html($data['order']->get_shipping_first_name() ?: $data['order']->get_billing_first_name()); ?> <?php echo esc_html($data['order']->get_shipping_last_name() ?: $data['order']->get_billing_last_name()); ?></td>
          <td class=td-qr-code>
            <img width="200" src="<?php echo esc_url($data['qr_url']); ?>">
          </td>
          <td class="delivery-label-text" text-rotate="-90"><?php echo esc_html($data['contact_name']); ?></td>
        </tr>
        <tr align="center">
          <td colspan="3" class="delivery-label-text"><?php echo esc_html($data['sequence_number']); ?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
