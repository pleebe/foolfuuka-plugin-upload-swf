<?php

use Foolz\FoolFrame\Model\Context;
use Foolz\Plugin\Event;

class HHVM_UploadSWF
{
    public function run()
    {
        Event::forge('Foolz\Plugin\Plugin::execute#foolz/foolfuuka-plugin-upload-swf')
            ->setCall(function ($result) {
                /* @var Context $context */
                $context = $result->getParam('context');

                /** @var Autoloader $autoloader */
                $autoloader = $context->getService('autoloader');
                $autoloader->addClassMap([
                    'Foolz\FoolFrame\Controller\Admin\Plugins\SWF' => __DIR__.'/classes/controller/admin.php',
                    'Foolz\FoolFuuka\Plugins\UploadSWF\Model\SWF' => __DIR__.'/classes/model/swf.php'
                ]);

                $context->getContainer()
                    ->register('foolfuuka-plugin.upload_swf', 'Foolz\FoolFuuka\Plugins\UploadSWF\Model\SWF')
                    ->addArgument($context);

                Event::forge('Foolz\FoolFrame\Model\Context::handleWeb#obj.afterAuth')
                    ->setCall(function ($object) use ($context) {
                        if ($context->getService('auth')->hasAccess('maccess.admin')) {
                            $context->getRouteCollection()->add(
                                'foolfuuka.plugin.upload_swf.admin',
                                new \Symfony\Component\Routing\Route(
                                    '/admin/plugins/swf/{_suffix}',
                                    [
                                        '_suffix' => 'manage',
                                        '_controller' => 'Foolz\FoolFrame\Controller\Admin\Plugins\SWF::manage'
                                    ],
                                    [
                                        '_suffix' => '.*'
                                    ]
                                )
                            );

                            Event::forge('Foolz\FoolFrame\Controller\Admin::before#var.sidebar')
                                ->setCall(function ($object) {
                                    $sidebar = $object->getParam('sidebar');
                                    $sidebar[]['plugins'] = [
                                        'content' => [
                                            'swf/manage' => [
                                                'level' => 'admin',
                                                'name' => 'SWF Preferences',
                                                'icon' => 'icon-file'
                                            ]
                                        ]
                                    ];
                                    $object->setParam('sidebar', $sidebar);
                                });
                        }
                    });

                Event::forge('Foolz\FoolFuuka\Model\MediaFactory::forgeFromUpload#var.config')
                    ->setCall(function ($object) use ($context) {
                        $auth = $context->getService('auth');
                        $pref = $context->getService('preferences');

                        if (
                            $auth->hasAccess('maccess.admin')
                            || ($auth->hasAccess('maccess.mod') && $pref->get('foolfuuka.plugins.upload_swf.allow_mods'))
                            || $pref->get('foolfuuka.plugins.upload_swf.allow_users')
                        ) {
                            $context->getService('foolfuuka-plugin.upload_swf')->updateConfig($object);
                        }
                    });

                Event::forge('Foolz\FoolFuuka\Model\Media::insert#var.media')
                    ->setCall(function ($object) use ($context) {
                        $auth = $context->getService('auth');
                        $pref = $context->getService('preferences');

                        $context->getService('foolfuuka-plugin.upload_swf')->processMedia($object);
                    });

                Event::forge('Foolz\FoolFuuka\Model\Media::insert#exec.createThumbnail')
                    ->setCall(function ($object) use ($context) {
                        $context->getService('foolfuuka-plugin.upload_swf')->processThumb($object);
                    });
            });
    }
}

(new HHVM_UploadSWF())->run();
