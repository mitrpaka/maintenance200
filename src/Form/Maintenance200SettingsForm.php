<?php

namespace Drupal\maintenance200\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class Maintenance200SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'maintenance200.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'maintenance200_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('maintenance200.settings');
    $form['explanation'] = array(
      '#type' => 'markup',
      '#markup' => 'Here, you can enable or disable the maintenance200 status code change functionality, and also set the status code that will be used if the functionality is enabled.'
    );
    $form['maintenance200_enabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Change the status code during maintenance mode'),
      '#default_value' => $config->get('maintenance200_enabled'),
    );
    $form['maintenance200_status_code'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Status code to use'),
      '#maxlength' => '3',
      '#size' => '3',
      '#required' => TRUE,
      '#description' => $this->t('A valid HTTP status code is required. See the Wikipedia <a href="http://en.wikipedia.org/wiki/List_of_HTTP_status_codes">HTTP status codes</a> page for a complete list.'),
      '#default_value' => $config->get('maintenance200_status_code'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    if ($form_state->getValue('maintenance200_status_code')) {
      $http_status_codes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended'
      );
      if (!array_key_exists($form_state->getValue('maintenance200_status_code'), $http_status_codes)) {
        $form_state->setError($form['maintenance200_status_code'], $this->t('A valid HTTP status code is required.'));
      }
    } else {
      $form_state->setError($form['maintenance200_status_code'], $this->t('You must provide an HTTP status code.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('maintenance200.settings')
      ->set('maintenance200_enabled', $form_state->getValue('maintenance200_enabled'))
      ->set('maintenance200_status_code', $form_state->getValue('maintenance200_status_code'))
      ->save();
    parent::submitForm($form, $form_state);
  }
  
}
