<?php
require_once 'SingletonTrait.php';
require_once 'Helper.php';

/**
 * Class UploadHelper
 *
 * @author A Vijay<mailvijay.vj@gmail.com>
 */
class UploadHelper
{
    use SingletonTrait;

    public const TYPE_IMPORT = 'import';

    /**
     * @var false|string
     */
    private $basePath;
    /**
     * @var false|string
     */
    private $root;

    /**
     * @var string
     */
    private string $path;
    /**
     * @var string
     */
    private string $type;
    /**
     * @var array
     */
    private array $file;
    /**
     * @var string
     */
    private string $fileName;

    /**
     * UploadHelper constructor.
     */
    public function __construct()
    {
        $this->basePath = realpath('./uploads');
        $this->root = realpath('./');
    }


    /**
     * @param $path
     * @return $this
     */
    public function setPath($path): self
    {
        $path = ltrim($path, DIRECTORY_SEPARATOR);
        $this->path = sprintf('%s%s%s', $this->basePath, DIRECTORY_SEPARATOR, $path);

        $this->createDirectoryIfNotExists($this->path);

        return $this;
    }

    /**
     * Method to create directory recursively
     * @param $path
     */
    private function createDirectoryIfNotExists($path): void
    {
        $stack = [];
        $localPath = $path;
        while (true) {
            # Checking whether the path exists
            if ($this->check($localPath)) {

                $this->initDirectoryIndex($localPath);

                /**
                 * Breaking the loop if given directory is exists
                 */
                if (count($stack) === 0) {
                    break;
                }
                /**
                 * After finding an existing directory creating the stacked directory to
                 * existing directory, So this loop will continue until the given paths
                 * valid / exists
                 */
                $localPath .= sprintf('%s%s', DIRECTORY_SEPARATOR, array_pop($stack));
                $this->initDirectory($localPath);
                continue;
            }
            /**
             * If path doest exists then pushing the current directory to stack
             * and will check current directories parent directory exist in loop
             *
             * This process will continue until given path existing
             */
            $stack[] = basename($localPath);
            $localPath = dirname($localPath);
        }
    }

    /**
     * @param $path
     * @param bool $isFile
     *
     * @return bool
     */
    public function check($path, $isFile = false): bool
    {
        return file_exists($path) && ($isFile ? !is_dir($path) : true);
    }

    /**
     * @param $path
     */
    private function initDirectory($path): void
    {
        $oldUmask = umask(0); // disable umask
        if (!@mkdir($path, 0755) && !is_dir($path)) {
            umask($oldUmask); // reset the umask
            throw new RuntimeException(sprintf('Permission denied on %s', $path));
        }
        umask($oldUmask); // reset the umask
        # To prevent directory listing
        $this->initDirectoryIndex($path);
    }

    /**
     * @param $path
     */
    private function initDirectoryIndex($path): void
    {
        $files = ['index.php' => '', '.htaccess' => 'Options -Indexes'];

        foreach ($files as $tmpPath => $content) {
            $tmpPath = sprintf('%s%s%s', $path, DIRECTORY_SEPARATOR, $tmpPath);
            if ($this->check($tmpPath)) {
                continue;
            }
            file_put_contents($tmpPath, $content);
        }
    }

    /**
     * @param mixed $file
     * @return UploadHelper
     */
    public function setFile($file): UploadHelper
    {
        $this->file = $file;

        $name = sprintf('%s%s', time(), Helper::generateRandomString(5));

        $_name = Helper::getArrayValue($file, 'name');
        if ($_name !== null) {
            $name .= '.' . pathinfo($_name, PATHINFO_EXTENSION);
        }
        $this->fileName = $name;

        return $this;
    }

    /**
     * @param bool $withFileName
     * @return string
     */
    public function getPath($withFileName = false): string
    {
        $path = $this->path;
        if ($withFileName === true && $this->fileName !== null) {
            $path .= sprintf('%s%s', DIRECTORY_SEPARATOR, $this->fileName);
        }

        return $path;
    }

    /**
     * @return bool
     */
    public function upload(): bool
    {
        $path = $this->getPath(true);

        return move_uploaded_file(Helper::getArrayValue($this->file, 'tmp_name'), $path);
    }

    /**
     * @param $path
     *
     * @return string
     */
    public function getRealPath($path): string
    {
        $path = ltrim(str_ireplace($this->root, '', $path), DIRECTORY_SEPARATOR);
        /**
         * @note: DIRECTORY_SEPARATOR is different for linux based system and windows so, replacing it
         *       with forward slashes for web accessible URL
         */
        return str_replace([DIRECTORY_SEPARATOR], '/', $path);
    }

    /**
     * @param $path
     *
     * @return string
     */
    public function getAbsPath($path): string
    {
        $path = ltrim($path, DIRECTORY_SEPARATOR);
        $appRoot = $this->root;

        $match = ltrim($appRoot, DIRECTORY_SEPARATOR);
        $match = preg_quote($match, DIRECTORY_SEPARATOR);
        $match = sprintf('/%s/', $match);

        if (preg_match($match, $path) !== 0) {
            return DIRECTORY_SEPARATOR . $path;
        }

        return $appRoot . DIRECTORY_SEPARATOR . $path;
    }
}