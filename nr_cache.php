<?php
	class NoseRub_cache extends SimplePie_Cache {
		/**
		 * Create a new SimplePie_Cache object
		 *
		 * @static
		 * @access public
		 */
		function create($location, $filename, $extension)
		{
			return new NoseRub_db_cache($location, $filename, $extension);
		}
		
	}
	class NoseRub_db_cache {

		function save($data){
			if (is_a($data, 'SimplePie'))
			{
				$data = $data->data;
			}
			update_option("nr_feedcache_ts",time());
			return update_option("nr_feedcache",serialize($data));
		}
		
		function load(){
			return unserialize(get_option("nr_feedcache"));
		}
		
		function mtime(){
			$x = get_option("nr_feedache_ts");
			if(!$x){
				$x = false;
			}
			return $x;
		}
		
		function touch(){
			update_option("nr_feedcache_ts",time());
		}
		function unlink(){
			delete_option("nr_feedcache");
			delete_option("nr_feedcache_ts");
		}
	}
?>