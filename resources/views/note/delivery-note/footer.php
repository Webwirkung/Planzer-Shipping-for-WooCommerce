<div style="font-weight:normal; text-align: center; font-size: 10px; line-height: 16px;font-style: normal;">
  <b><?php echo esc_html($data['company']); ?></b> <?php echo esc_html($data['company_address']->get_base_address()); ?>, <?php echo esc_html($data['company_address']->get_base_postcode()); ?> <?php echo esc_html($data['company_address']->get_base_city()); ?>
  <b><?php echo __('E-Mail:', 'planzer'); ?></b> <?php echo esc_html($data['email']); ?>
  <b><?php echo __('Website:', 'planzer'); ?></b> <?php echo esc_html($data['website']); ?>
</div>