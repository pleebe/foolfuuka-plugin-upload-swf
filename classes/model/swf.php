<?php

namespace Foolz\FoolFuuka\Plugins\UploadSWF\Model;

use Foolz\FoolFrame\Model\Context;
use Foolz\FoolFrame\Model\Model;
use Foolz\FoolFrame\Model\Preferences;

class SWF extends Model
{
    /**
     * @var Preferences
     */
    protected $preferences;

    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->preferences = $context->getService('preferences');
    }
    public function updateConfig($object)
    {
        $extensions = $object->getParam('ext_whitelist');
        $mime_types = $object->getParam('mime_whitelist');

        array_push($extensions, 'swf');
        array_push($mime_types, 'application/x-shockwave-flash');

        $object->setParam('ext_whitelist', $extensions);
        $object->setParam('mime_whitelist', $mime_types);
    }

    public function processMedia($object)
    {

        if ($object->getParam('file')->getMimeType() == 'application/x-shockwave-flash') {
		
            if ($this->preferences->get('foolfuuka.plugins.upload_swf.binary_path')) {
                if($this->preferences->get('foolfuuka.plugins.upload_swf.gnashrc')) {
                    $export = 'GNASHRC='.$this->preferences->get('foolfuuka.plugins.auto_thumbnailer.gnashrc');
                } else {
                    $export = '';
                }
                exec("$export ".$this->preferences->get('foolfuuka.plugins.upload_swf.binary_path')." --screenshot last --screenshot-file \"".$object->getParam('path').".png\" \"".$object->getParam('path')."\" --max-advances=100 --timeout=100 -r1");
                $object->setParam('preview_orig', $object->getParam('time').'s.png');
            }
        }
    }

    public function processThumb($object)
    {
        if ($object->getParam('media')->getMimeType() == 'application/x-shockwave-flash') {
            exec($object->getParam('exec') .
                " " . $object->getParam('media')->getPathname() . ".png[0] -quality 80 -background none " .
                "-resize \"" . $object->getParam('thumb_width') . "x" . $object->getParam('thumb_height') .
                ">\" " . $object->getParam('thumb'));

            $object->set('done');
        }
    }
}
