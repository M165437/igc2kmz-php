<?php
/**
 * @author    Michael Schmidt-Voigt
 * @since     2018-05-09
 * @copyright 2018 (c) Michael Schmidt-Voigt
 * @package   Igc2KmzPhp
 */

namespace M165437\Igc2KmzPhp;

use Symfony\Component\Process\Process;

interface Igc2KmzInterface
{
    /**
     * Set output filename
     *
     * Help:
     *   -o FILENAME, --output=FILENAME
     *
     * @param string $path
     * @return $this
     */
    public function output($path);

    /**
     * Set timezone offset
     *
     * Help:
     *   -z HOURS, --tz-offset=HOURS
     *
     * @param integer $hours
     * @return $this
     */
    public function tzOffset($hours);

    /**
     * Add root element
     *
     * Help:
     *  -r FILENAME, --root=FILENAME
     *
     * @param string $path
     * @return $this
     */
    public function root($path);

    /**
     * Set task
     *
     * Help:
     *   -t FILENAME, --task=FILENAME
     *
     * @param string $path
     * @return $this
     */
    public function task($path);

    /**
     * Per-flight options:
     */

    /**
     * Set flight IGC file
     *
     * Help:
     *   -i FILENAME, --igc=FILENAME
     *
     * @param string $path
     * @return $this
     */
    public function igc($path);

    /**
     * Set pilot name
     *
     * Help:
     *   -n STRING, --pilot-name=STRING
     *
     * @param string $name
     * @return $this
     */
    public function pilotName($name);

    /**
     * Set glider type
     *
     * Help:
     *   -g STRING, --glider-type=STRING
     *
     * @param string $type
     * @return $this
     */
    public function gliderType($type);

    /**
     * Set track line color
     *
     * Help:
     *   -c COLOR, --color=COLOR
     *
     * @param string $color
     * @return $this
     */
    public function color($color);

    /**
     * Set track line width
     *
     * Help:
     *   -w INTEGER, --width=INTEGER
     *
     * @param integer $width
     * @return $this
     */
    public function width($width);

    /**
     * Set flight URL
     *
     * Help:
     *   -u URL, --url=URL
     *
     * @param string $url
     * @return $this
     */
    public function url($url);

    /**
     * Set flight XC
     *
     * Help:
     *   -x FILENAME, --xc=FILENAME
     *
     * @param string $path
     * @return $this
     */
    public function xc($path);

    /**
     * Per-photo options:
     */

    /**
     * Add photo with comment
     *
     * Help:
     *  -p URL, --photo=URL
     *  -d STRING, --description=STRING
     *
     * @param string $url
     * @param string $description
     * @return $this
     */
    public function addPhoto($url, $description = '');

    /**
     * Get the raw associative photos array.
     *
     * E.g.:
     * ```php
     * ['--photo' => 'path', '--description' => 'comment']
     * ```
     *
     * @return array
     */
    public function getPhotos();

    /**
     * Reset the photos / descriptions arguments.
     *
     * @return $this;
     */
    public function resetPhotos();

    /**
     * Build the process and return it without running it.
     *
     * The method will be called by `\Igc2KmzPhp\Igc2KmzInterface::run`
     * You can use it, to inspect the process before it is run.
     *
     * To run the process, pass it to `\Igc2KmzPhp\Igc2KmzInterface::run`,
     * the method will do exception handling for you and will return the result.
     *
     * @return Process
     */
    public function build();

    /**
     * Run igc2kmz command.
     *
     * @return string
     * @param Process $process (optional) the process obj if you fetched it from `\Igc2KmzPhp\Igc2KmzInterface::build`
     *                         Otherwise, the method will build the process on its own using the aforementioned method.
     * @throws \Exception
     */
    public function run(Process $process = null);

    /**
     * Get the raw associative options array.
     *
     * E.g.:
     * ```php
     * ['--output' => 'path', '--format' => 'json']
     * ```
     *
     * @return array
     */
    public function getOptions();

    /**
     * Reset the argument list to not keep any state.
     *
     * @return $this;
     */
    public function resetOptions();

    /**
     * Get the igc2kmz binary path that was set.
     *
     * @return string
     */
    public function getBinary();

    /**
     * Set the path to the igc2kmz binary.
     * This method will neither check if the binary exists, nor will it do any other validation.
     *
     * @param string $binary
     * @return $this
     */
    public function setBinary($binary);
}
