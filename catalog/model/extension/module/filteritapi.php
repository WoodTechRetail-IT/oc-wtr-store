<?php
/*
@author  Dmitriy Kubarev
@link  http://www.simpleopencart.com
*/

class ModelModuleFilteritApi extends Model {
    public function test($address) {
      return true;
    }
}

class ModelExtensionModuleFilteritApi extends ModelModuleFilteritApi {
}