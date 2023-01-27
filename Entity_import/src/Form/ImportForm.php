<?php

namespace Drupal\Entity_import\Form;

/**
 * @file
 * Contains \Drupal\Entity_import\Form\ImportForm.
 */
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements class ImportForm.
 */
class ImportForm extends FormBase {

  /**
   * The storage handler class for files.
   *
   * @var \Drupal\file\FileStorage
   */
  protected $fileStorage;

  /**
   * This is an example.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity
   *   The Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity) {
    $this->fileStorage = $entity->getStorage('file');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'example_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['description'] = [
      '#markup' => '<p>Use this form to upload a CSV file of Data</p>',
    ];

    $form['import_csv'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload file here'),
      '#upload_location' => 'public://importcsv/',
      '#default_value' => '',
      "#upload_validators"  => ["file_validate_extensions" => ["csv"]],
      '#states' => [
        'visible' => [
          ':input[name="File_type"]' => ['value' => $this->t('Upload Your File')],
        ],
      ],
    ];

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Upload CSV'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /* Fetch the array of the file stored temporarily in database */
    $csv_file = $form_state->getValue('import_csv');

    /* Load the object of the file by it's fid */
    $file = $this->fileStorage->load($csv_file[0]);

    /* Set the status flag permanent of the file object */
    $file->setPermanent();

    /* Save the file in database */
    $file->save();

    // If you need to work on how data is extracted, process it here.
    $data = $this->csvtoarray($file->getFileUri(), ',');
    foreach ($data as $row) {
      $operations[] = ['\Drupal\Entity_import\AddImportContent::addImportContentItem',
      [$row],
      ];
    }

    $batch = [
      'title' => $this->t('Importing Data...'),
      'operations' => $operations,
      'init_message' => $this->t('Import is starting.'),
      'finished' => '\Drupal\Entity_import\AddImportContent::addImportContentItemCallback',
    ];
    batch_set($batch);
  }

  /**
   * {@inheritdoc}
   */
  public function csvtoarray($filename = '', $delimiter) {

    if (!file_exists($filename) || !is_readable($filename)) {
      return FALSE;
    }
    $header = NULL;
    $data = [];

    if (($handle = fopen($filename, 'r')) !== FALSE) {
      while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
        if (!$header) {
          $header = $row;
        }
        else {
          $data[] = array_combine($header, $row);
        }
      }
      fclose($handle);
    }

    return $data;
  }

}
