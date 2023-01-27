<?php

namespace Drupal\Entity_import\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements class ImportPage.
 */
class ImportPage extends ControllerBase {
  /**
   * Form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Class constructor.
   */
  public function __construct($formBuilder) {
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('form_builder')
    );
  }

  /**
   * Display the markup.
   *
   * @return array
   *   An array containing a display of markup.
   */
  public function content(Request $request) {

    $form = $this->formBuilder->getForm('Drupal\Entity_import\Form\ImportForm');

    return $form;
  }

}
