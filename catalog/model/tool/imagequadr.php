<?php
class ModelToolImagequadr extends Model {
	public function resize($filename, $width, $height) {
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$image_old = $filename;
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

		if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
				 
			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) { 
				return DIR_IMAGE . $image_old;
			}
						
			$path = '';

			$directories = explode('/', dirname($image_new));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			if ($width_orig != $width || $height_orig != $height) {

                $scale_hor = $width_orig / $width;
                $scale_ver = $height_orig / $height;

                $image = new Image(DIR_IMAGE . $image_old);

                if ($scale_ver > $scale_hor) {
                    $diff = $height * $scale_hor;
                    $top_x = 0;
                    $top_y = ($height_orig - $diff) / 2;
                    $bottom_x = $width_orig;
                    $bottom_y = $top_y + $diff;
                    $image->crop($top_x, $top_y, $bottom_x, $bottom_y);
                } elseif ($scale_ver < $scale_hor) {
                    $diff = $width * $scale_ver;
                    $top_x = ($width_orig - $diff) / 2;
                    $top_y = 0;
                    $bottom_x = $top_x + $diff;
                    $bottom_y = $height_orig;
                    $image->crop($top_x, $top_y, $bottom_x, $bottom_y);
                }

                $image->resize($width, $height);
                $image->save(DIR_IMAGE . $image_new);

            } elseif (false) {
				$image = new Image(DIR_IMAGE . $image_old);
				$image->resize($width, $height);
				$image->save(DIR_IMAGE . $image_new);
			} else {
				copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
			}
		}
		
		$image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +
		
		if ($this->request->server['HTTPS']) {
			return $this->config->get('config_ssl') . 'image/' . $image_new;
		} else {
			return $this->config->get('config_url') . 'image/' . $image_new;
		}
	}
}
