#Drupal 8 in 2 steps
Drupal 8 is the latest version of Drupal, a modern, PHP 5.4-boasting, REST-capable, object-oriented powerhouse.
The concepts are still the same as the previous versions but the approach is now different. Drupal 8 comes with a modern Object Oriented Programming (OOP) approach to most parts of the system thanks to the use of the [Symfony2](http://symfony.com) framework.

I took part in the Drupalcon in Amsterdam and I enjoyed a number of really interesting talks about Drupal 8, among those 'Drupal 8: The Crash Course' realized and presented by [Larry Garfield](https://www.drupal.org/user/26398). In this post the idea is to recap few key points of his talk as I think they are important to fully understand the basics of this new Drupal version. In case you are interested you can also [watch the full talk](https://amsterdam2014.drupal.org/session/drupal-8-crash-course).

##How do I define a module?
In Drupal 8 to define a module we need only a YAML (.info.yml) file:

**/modules/d8_example_module/d8_example_module.info.yml**

    name: D8 Test Module
    description: D8 Test Module
    type: module
    core: 8.x
    package: Custom

In Drupal 8 the .module file is not required anymore, so with only the .info.yml file the module is ready to be enabled.

##How do I make a page?
Start creating a controller extending the ControllerBase class and return the output of the page:

**/modules/d8_example_module/src/Controller/D8ExampleModuleController.php**

    namespace Drupal\d8_example_module\Controller;

    use Drupal\Core\Controller\ControllerBase;

    class D8ExampleModuleController extends ControllerBase {

      public function test_page($from, $to) {
        $message = $this->t('%from to %to', [
          '%from' => $from,
          '%to' => $to,
        ]);

        return ['#markup' => $message];
      }
    }

Once this is done, within the .routing.yml file we can define the path, the controller, the title and the permissions:

**/modules/d8_example_module/d8_example_module.routing.yml**

    d8_example_module.test_page:
      path: '/test-page/{from}/{to}'
      defaults:
        _controller: 'Drupal\d8_example_module\Controller\D8ExampleModuleController::test_page'
        _title: 'Test Page!'
      requirements:
        _permission: 'access content'

##How do I make content themeable?
We still have the hook_theme() function to define our theme:

**/modules/d8_example_module/d8_example_module.module**

    /**
     * Implements hook_theme().
     */
    function d8_example_module_theme() {
      $theme['d8_example_module_page_theme'] = [
        'variables' => ['from' => NULL, 'to' => NULL],
        'template' => 'd8-theme-page',
      ];

      return $theme;
    }

For the template page Drupal 8 uses **Twig**, a [third-party template language](http://twig.sensiolabs.org/documentation) used by many PHP projects. For more info about Twig have a look at [Twig in Drupal 8](https://www.drupal.org/theme-guide/8/twig). One of the cool parts of Twig is that we can do string translation directly in the template file:

**/modules/d8_example_module/template/d8-theme-page.html.twig**

    <section>
      {% trans %}
        <strong>{{ from }}</strong> to <em>{{ to }}</em>
      {% endtrans %}
    </section>

And then we assign the theme to the page:

**/modules/d8_example_module/src/Controller/D8ExampleModuleController.php**

    namespace Drupal\d8_example_module\Controller;

    use Drupal\Core\Controller\ControllerBase;

    class D8ExampleModuleController extends ControllerBase {

      public function test_page($from, $to) {
        return [
          '#theme' => 'd8_example_module_page_theme',
          '#from' => $from,
          '#to' => $to,
        ];
      }
    }

##How do I define a variable?
Drupal 8 has a whole new configuration system that uses human-readable [YAML](http://www.yaml.org/) (.yml) text files to store configuration items. For more info have a look at [Managing configuration in Drupal 8](https://www.drupal.org/documentation/administer/config).

We define variables in config/install/*.settings.yml:

**/modules/d8_example_module/config/install/d8_example_module.settings.yml**

    default_count: 3

The variables will be stored in the database during the installation of the module. We define the schema for the variables in config/schema/*.settings.yml:

**/modules/d8_example_module/config/schema/d8_example_module.settings.yml**

    d8_example_module.settings:
      type: mapping
      label: 'D8 Example Module settings'
      mapping:
        default_count:
          type: integer
          label: 'Default count'

##How do I make a form?
To create a form we extend a ConfigFormBase class:

**/modules/d8_example_module/src/Form/TestForm.php**

    namespace Drupal\d8_example_module\Form;

    use Drupal\Core\Form\ConfigFormBase;
    use Drupal\Core\Form\FormStateInterface;

    class TestForm extends ConfigFormBase {
      public function getFormId() {
        return 'test_form';
      }

      public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('d8_example_module.settings');

        $form['default_count'] = [
          '#type' => 'number',
          '#title' => $this->t('Default count'),
          '#default_value' => $config->get('default_count'),
        ];
        return parent::buildForm($form, $form_state);
      }

      public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = $this->config('d8_example_module.settings');
        $config->set('default_count', $form_state->getValue('default_count'));
        $config->save();
        parent::submitForm($form, $form_state);
      }

      protected function getEditableConfigNames() {
        return ['d8_example_module.settings'];
      }
    }

Then within the .routing.yml file we can define the path, the form, the title and the permissions:

**/modules/d8_example_module/d8_example_module.routing.yml**

    d8_example_module.test_form:
      path: /admin/config/system/test-form
      defaults:
        _form: 'Drupal\d8_example_module\Form\TestForm'
        _title: 'Test Form'
      requirements:
        _permission: 'configure_form'

We use another YAML file (.permissions.yml) to define permissions:

**/modules/d8_example_module/d8_example_module.permissions.yml**

    'configure_form':
      title: 'Access to Test Form'
      description: 'Set the Default Count variable'

We also use another YAML file (.links.menu.yml) to define menu links:

**/modules/d8_example_module/d8_example_module.links.menu.yml**

    d8_example_module.test_form:
      title: 'Test Form'
      description: 'Set the Default Count variable'
      route_name: d8_example_module.test_form
      parent: system.admin_config_system

##How do I make a block?
To create a block we extend a ConfigFormBase class:

**/modules/d8_example_module/src/Plugin/Block/TestBlock.php**

    namespace Drupal\d8_example_module\Plugin\Block;

    use Drupal\Core\Block\BlockBase;

    /**
     * Test Block.
     *
     * @Block(
     *   id = "test_block",
     *   admin_label = @Translation("Test Block"),
     *   category = @Translation("System")
     * )
     */
    class TestBlock extends BlockBase {

      public function build() {
        return [
          '#markup' => $this->t('Block content...'),
        ];
      }
    }

In this way the block is ready to be configured in the CMS (/admin/structure/block).
Here is an example of a more complex block:

    namespace Drupal\d8_example_module\Plugin\Block;

    use Drupal\Core\Block\BlockBase;
    use Drupal\Core\Form\FormStateInterface;

    /**
     * Test Block.
     *
     * @Block(
     *   id = "test_block",
     *   admin_label = @Translation("Test Block"),
     *   category = @Translation("System")
     * )
     */
    class TestBlock extends BlockBase {

      public function defaultConfiguration() {
        return ['enabled' => 1];
      }

      public function blockForm($form, FormStateInterface $form_state) {
        $form['enabled'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Configuration enabled'),
          '#default_value' => $this->configuration['enabled'],
        ];

        return $form;
      }

      public function blockSubmit($form, FormStateInterface $form_state) {
        $this->configuration['enabled'] = (bool)$form_state->getValue('enabled');
      }

      public function build() {
        if ($this->configuration['enabled']) {
          $message = $this->t('Configuration enabled');
        }
        else {
          $message = $this->t('Configuration disabled');
        }
        return [
          '#markup' => $message,
        ];
      }
    }

##Structure of a module
The structure of a module should look like the example module **d8_example_module**:

    d8_example_module/
     |
     |- config/
       |
       |- install/
         |
         |- d8_example_module.setting.yaml
       |
       |- schema/
         |
         |- d8_example_module.settings.yaml
     |
     |- src/
       |
       |- Controller/
         |
         |- D8ExampleModuleController.php
       |
       |- Form/
         |
         |- TestForm.php
       |
       |- Plugin/
         |
         |- Block/
           |
           |- TestBlock.php
     |
     |- templates/
       |
       |- d8-theme-page.html.twig
     |
     |- d8_example_module.info.yml
     |
     |- d8_example_module.links.menu.yml
     |
     |- d8_example_module.module
     |
     |- d8_example_module.permissions.yml
     |
     |- d8_example_module.routing.yml

**Drupal 8 in 2 steps: Extend a base Class or implement an Interface and tell Drupal about it.**

##Testing the Module

There are a number of things you need to do and this is easily done with a browser that support "private" browsing as this simplifies testing. There are a few things to test:

* controller based test page
* themed test page
* the block
* the block's configuration
* new permission
* the config form

This can be easily achived with the following steps:

* Private Sesssion
  * browse to localhost/test-page/London/Paris
  * browse to localhost/test-page-2/London/Paris
  * browse to localhost/admin/config/system/test-form (access denied)
* Normal session, logged in as administrator
  * add the block, Test Block
  * confirm it displays
  * change the block configuration and clear the "Configuration enabled" check box
  * confirm the block display has changed
  * change the permissions so that "Anonymous user" has "Access to Test Form"
* Private Session
  * browse to localhost/admin/config/system/test-form
  * change the value on the config form, check it saves correctly
