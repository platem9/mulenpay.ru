
<?php

namespace Drupal\mulen_payment\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class MulenPaymentSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return ['mulen_payment.settings'];
  }

  public function getFormId() {
    return 'mulen_payment_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('mulen_payment.settings');

    $form['shop_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Shop ID'),
      '#default_value' => $config->get('shop_id'),
    ];

    $form['secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret Key'),
      '#default_value' => $config->get('secret_key'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('mulen_payment.settings')
      ->set('shop_id', $form_state->getValue('shop_id'))
      ->set('secret_key', $form_state->getValue('secret_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
