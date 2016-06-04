<?php

namespace Foolz\FoolFrame\Controller\Admin\Plugins;

use Foolz\FoolFrame\Model\Validation\ActiveConstraint\Trim;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SWF extends \Foolz\FoolFrame\Controller\Admin
{
    public function before()
    {
        parent::before();

        $this->param_manager->setParam('controller_title', 'SWF Preferences');
    }

    public function security()
    {
        return $this->getAuth()->hasAccess('maccess.admin');
    }

    function structure()
    {
        return [
            'open' => [
                'type' => 'open'
            ],
            'foolfuuka.plugins.upload_swf.binary_path' => [
                'preferences' => true,
                'type' => 'input',
                'label' => _i('Full path to dump-gnash.'),
                'class' => 'span3',
                'validation' => [new Trim()]
            ],
            'foolfuuka.plugins.upload_swf.gnashrc' => [
                'preferences' => true,
                'type' => 'input',
                'label' => _i('Gnashrc file location'),
                'help' => _i('This is useful for setting security limits to Gnash. Example file in plugin directory "private".'),
                'class' => 'span3',
                'validation' => [new Trim()]
            ],
            'foolfuuka.plugins.upload_swf.allow_mods' => [
                'preferences' => true,
                'type' => 'checkbox',
                'help' => _i('Allow Moderators to upload SWF files.')
            ],
            'foolfuuka.plugins.upload_swf.allow_users' => [
                'preferences' => true,
                'type' => 'checkbox',
                'help' => _i('Allow Users to upload SWF files.')
            ],
            'submit' => [
                'type' => 'submit',
                'class' => 'btn-primary',
                'value' => _i('Submit')
            ],
            'close' => [
                'type' => 'close'
            ],
        ];
    }

    function action_manage()
    {
        $this->param_manager->setParam('method_title', 'Manage');

        $data['form'] = $this->structure();

        $this->preferences->submit_auto($this->getRequest(), $data['form'], $this->getPost());

        $this->builder->createPartial('body', 'form_creator')
            ->getParamManager()->setParams($data);

        return new Response($this->builder->build());
    }
}
