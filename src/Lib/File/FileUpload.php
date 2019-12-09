<?php

namespace KarmaFW\Lib\File;


class FileUpload 
{
	protected $tmp_path = null;
	protected $new_path = null;
	protected $filename = null;
	protected $file_ext = null;
	protected $file_size = null;
	protected $content_type = null;
	protected $upload_dir = '/tmp';


	public function __construct($file, $storage_dir=null)
	{
		if (! isset($file['error'])) {
			return;
		}

		if (! empty($file['error'])) {
			// 0: OK
			// 1: UPLOAD_ERR_INI_SIZE
			// 2: UPLOAD_ERR_FORM_SIZE
			// 3: UPLOAD_ERR_PARTIAL
			// 4: UPLOAD_ERR_NO_FILE
			// 6: UPLOAD_ERR_NO_TMP_DIR
			// 7: UPLOAD_ERR_CANT_WRITE
			// 8: UPLOAD_ERR_EXTENSION
			return;
		}

		//pre($file, 1);


		$this->filename = basename($file['name']);
		$this->tmp_path = $file['tmp_name'];
		
		$file_name_parts = explode('.', $this->filename);
		$this->file_ext = strtolower($file_name_parts[ count($file_name_parts)-1 ]);

		$this->file_size = filesize($this->tmp_path);
		$this->content_type = $file['type'];
	}


	public function hasFile()
	{
		return ! empty($this->tmp_path);
	}


	public function getTmpPath()
	{
		return $this->tmp_path;
	}


	public function getFilename()
	{
		return $this->filename;
	}


	public function getSize()
	{
		// Alias of getFileSize()
		return $this->getFileSize();
	}

	public function getFileSize()
	{
		return $this->file_size;
	}


	public function getExt()
	{
		// Alias of getFileExtension()
		return $this->getFileExtension();
	}

	public function getExtension()
	{
		// Alias of getFileExtension()
		return $this->getFileExtension();
	}

	public function getFileExtension()
	{
		return $this->file_ext;
	}


	public function getContentType()
	{
		return $this->content_type;
	}


	public function setUploadDir($dir, $create_if_missing=false)
	{
		if (is_dir($dir) && is_writable($dir)) {
			// dossier existe et writable

		} else if (is_dir($dir)) {
			// dossier existe mais pas writeable
			return false;

		} else if (! $create_if_missing) {
			// dossier n'existe pas
			return false;

		} else {
			// dossier n'existe pas et on va le creer
			if (@mkdir($dir)) {
				// ok

			} else {
				// impossible de créer le dossier
				return false;
			}
		}

		$this->upload_dir = rtrim($dir, '/');

		return true;
	}



	public function read()
	{
		return file_get_contents( $this->new_path ? $this->new_path : $this->tmp_path );
	}


	public function store($filename)
	{
		if (empty($this->upload_dir) || $this->upload_dir == '/') {
			$new_path = '/' . $filename;

		} else {
			$new_path = $this->upload_dir . '/' . $filename;
		}

		$ok = move_uploaded_file($this->tmp_path, $new_path);

		if ($ok) {
			$this->new_path = $new_path;
			return $new_path;
		}

		return false;
	}
}
