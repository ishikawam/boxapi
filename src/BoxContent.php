<?php

namespace Maengkom\Box;

trait BoxContent {

	/*
	|
	| ================================= Folder API Methods ==================================
	| Check documentation here https://box-content.readme.io/reference#folder-object
	|
	*/

	/* Get the details of the mentioned folder */
	public function getFolderInfo($folder_id, $json = false) {
		$url = $this->api_url . "/folders/$folder_id";
		return $this->get($url, $json);
	}

	/* Get the list of items in the mentioned folder */
	public function getFolderItems($folder_id, $json = false, $option = null) {
		$url = $this->api_url . "/folders/$folder_id/items";
		if ($option) {
			$url .= '?' . http_build_query($option, '', '\&');
		}
		return $this->get($url, $json);
	}

	/* Create folder */
	public function createFolder($name, $parent_id, $json = false) {
		$url = $this->api_url . "/folders";
		$data = "-d '{\"name\":\"$name\", \"parent\": {\"id\": \"$parent_id\"}}'";
		return $this->post($url, $json, $data);
	}

	/* Update folder */
	public function updateFolder($folder_id, $folder_name, $json = false) {
		$url = $this->api_url . "/folders/$folder_id";
		$data = "-d '{\"name\":\"$folder_name\"}'";
		return $this->put($url, $json, $data);
	}

	/* Delete folder */
	public function deleteFolder($folder_id) {
		$url = $this->api_url . "/folders/$folder_id";
		return $this->delete($url);
	}

	/* Copy folder */
	public function copyFolder($folder_id, $folder_dest_id, $json = false) {
		$url = $this->api_url . "/folders/$folder_id/copy";
		$data = "-d '{\"parent\": {\"id\": \"$folder_dest_id\"}}'";
		return $this->post($url, $json, $data);
	}

	/* Create shared link folder */
	public function createSharedLinkFolder($folder_id, $json = false) {
		$url = $this->api_url . "/folders/$folder_id";
		$data = "-d '{\"shared_link\": {\"access\": \"open\"}}'";
		return $this->put($url, $json, $data);
	}

	/* Get folder collaborations */
	public function getFolderCollaborations($folder_id, $json = false) {
		$url = $this->api_url . "/folders/$folder_id/collaborations";
		return $this->get($url, $json);
	}

	/* Get trashed items */
	public function getTrashedItems($limit = 10, $offset = 0, $json = false) {
		$url = $this->api_url . "/folders/trash/items?limit=$limit&offset=$offset";
		return $this->get($url, $json);
	}

	/* Get trashed folder */
	public function getTrashedFolder($folder_id, $json = false) {
		$url = $this->api_url . "/folders/$folder_id/trash";
		return $this->get($url, $json);
	}

	/* Delete folder permanently */
	public function deleteFolderPermanent($folder_id) {
		$url = $this->api_url . "/folders/$folder_id/trash";
		return $this->delete($url);
	}

	/* Restore a folder */
	public function restoreFolder($folder_id, $newname = '', $json = false) {
		$url = $this->api_url . "/folders/$folder_id";
		if (empty($newname)) {
			$data = "-d '{\"name\": \"$newname\"}'";
		}
		return $this->post($url, $json, $data);
	}


	/*
	|
	| ================================= File API Methods ==================================
	| Check Box documentation here https://box-content.readme.io/reference#files
	|
	*/

	/* Get the details of the mentioned file */
	public function getFileInfo($file_id, $json = false) {
		$url = $this->api_url . "/files/$file_id";
		return $this->get($url, $json);
	}

	/* Update file */
	public function updateFileInfo($file_id, $file_name, $json = false) {
		$url = $this->api_url . "/files/$file_id";
		$data = "-d '{\"name\":\"$file_name\"}'";
		return $this->put($url, $json, $data);
	}

	/* Toggle lock or unlock a file */
	public function toggleLock($file_id, $lockType = null, $expire = null, $canDownload = false) {
		$url = $this->api_url . "/files/$file_id";
		$data = "-d '{\"lock\":{\"type\": \"$lockType\", \"expires_at\": \"$expire\", \"is_download_prevented\": $canDownload}'";
		return $this->put($url, $json, $data);
	}

	/* Download file */
	public function downloadFile($file_id) {

		//set the headers
		$headers = $this->auth_header_php;

		$curl = curl_init();

		//set the options
		curl_setopt($curl, CURLOPT_URL, $this->api_url . "/files/$file_id/content");
		curl_setopt($curl, CURLOPT_HTTPHEADER, array($headers));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //returns to a variable instead of straight to page
		curl_setopt($curl, CURLOPT_HEADER, true); //returns headers as part of output
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); //I needed this for it to work
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); //I needed this for it to work

		$headers = curl_exec($curl); //because the returned page is blank, this will include headers only

		return curl_getinfo($curl, CURLINFO_REDIRECT_URL);
	}

	/* Upload a file */
	public function uploadFile($filename ,$parent_id, $name = null, $json = false) {
		$url = $this->upload_url . '/files/content';

		if ( ! isset($name)) {
			$name = basename($filename);
		}

		$attributes = "-F attributes='{\"name\":\"$name\", \"parent\":{\"id\":\"$parent_id\"}}'";
		$attributes = $attributes . " -F file=@$filename";
		return $this->post($url, $json, $attributes);
	}

	/* Delete a file */
	public function deleteFile($file_id) {
		$url = $this->api_url . "/files/$file_id";
		return $this->delete($url);
	}

	/* Update a file - new upload to update content of file */
	public function updateFile($filename, $file_id, $json = false) {
		$url = $this->upload_url . '/files/$file_id/content';
		$attributes = $attributes . " -F file=@$filename";
		return $this->post($url, $json, $attributes);
	}

	/* Copy a file */
	public function copyFile($file_id, $folder_dest_id, $json = false) {
		$url = $this->api_url . "/files/$file_id/copy";
		$data = "-d '{\"parent\": {\"id\": \"$folder_dest_id\"}}'";
		return $this->post($url, $json, $data);
	}

	/* Get thumbnail of a file */
	public function getThumbnail($file_id, $min_height = '256', $min_width = '256', $max_height = '256', $max_width = '256') {

		$url = $this->api_url . "/files/$file_id/thumbnail.png?min_height=$min_height&min_width=$min_width&max_height=$min_width&max_width=$min_width";

		//set the headers
		$headers = $this->auth_header_php;

		$curl = curl_init();

		//set the options
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array($headers));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //returns to a variable instead of straight to page
		curl_setopt($curl, CURLOPT_HEADER, true); //returns headers as part of output
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); //I needed this for it to work
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); //I needed this for it to work

		$response = curl_exec($curl); //because the returned page is blank, this will include headers only

		// Then, after your curl_exec call:
		$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);

		return $body;

	}

	/* Get embed link of a file */
	public function getEmbedLink($file_id, $json = false) {
		$url = $this->api_url . "/files/$file_id?fields=expiring_embed_link";
		return $this->get($url, $json);
	}

	/* Share a file */
	public function createShareLink($file_id, $access, $json = false) {
		$url = $this->api_url . "/files/$file_id";
		$data = "-d '{\"shared_link\": {\"access\": \"$access\"}}'";
		return $this->put($url, $json, $data);
	}

	/* Get trashed file */
	public function getTrashedFile($file_id, $json = false) {
		$url = $this->api_url . "/files/$file_id/trash";
		return $this->get($url, $json);
	}

	/* Delete file permanently */
	public function deleteFilePermanent($file_id) {
		$url = $this->api_url . "/files/$file_id/trash";
		return $this->delete($url);
	}

	/* Restore a file */
	public function restoreItem($file_id, $newname = '', $json = false) {
		$url = $this->api_url . "/files/$file_id";
		if (empty($newname)) {
			$data = "-d '{\"name\": \"$newname\"}'";
		}
		return $this->post($url, $json, $data);
	}

	/* View comments */
	public function viewComments($file_id, $json = false) {
		$url = $this->api_url . "/files/$file_id/comments";
		return $this->get($url, $json);
	}

	/* Get file tasks */
	public function getFileTasks($file_id, $json = false) {
		$url = $this->api_url . "/files/$file_id/tasks";
		return $this->get($url, $json);
	}

    /* Search for files/folders */
    public function search($query, $type = '', $scope = 'user_content', $limit = 100, $json = false)
    {

        $url = $this->api_url . "/search?query=$query&type=$type&scope=$scope&limit=$limit";
        return $this->get($url, $json);
    }


	// ================================= Helper Methods ==================================

	protected function get($url, $json = false, $data = '') {
		$data = shell_exec("curl $url $this->auth_header $data");
		if ($json) {
			return $data;
		} else {
			return json_decode($data, true);
		}
	}

	protected function post($url, $json = false, $data = '') {
		$data = shell_exec("curl $url $this->auth_header $data -X POST");
		if ($json) {
			return $data;
		} else {
			return json_decode($data, true);
		}
	}

	protected function put($url, $json = false, $data = '') {
		$data = shell_exec("curl $url $this->auth_header $data -X PUT");
		if ($json) {
			return $data;
		} else {
			return json_decode($data, true);
		}
	}

	protected function delete($url) {
		$data = shell_exec("curl $url $this->auth_header -X DELETE");
		return $data;
	}

}
