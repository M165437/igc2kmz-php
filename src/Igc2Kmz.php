<?php
/**
 * Access to the igc2kmz binary.
 *
 * This component will provide a PHP interface to the igc2kmz binary.
 *
 * ```php
 * $kmz = $igc2kmz
 *  ->igc('path/to/flight.igc')
 *  ->tzOffset(5)
 *  ->output('path/to/flight.kmz')
 *  ->run();
 * ```
 *
 * Once igc2kmz is configured, the object instance will not reset its
 * arguments and options.
 * So running `$igc2kmz->run()` again immediately, will return the very same result.
 *
 * This behaviour allows you to re-use an igc2kmz instance without minimal config change per call.
 *
 * More about the internals of this wrapper:
 * Output of `igc2kmz.py --help`:
 *
 * ```
 * Usage: igc2kmz.py [options]
 *
 * IGC to Google Earth converter
 *
 * Options:
 *   -h, --help            show this help message and exit
 *   -o FILENAME, --output=FILENAME
 *                         set output filename
 *   -z HOURS, --tz-offset=HOURS
 *                         set timezone offset
 *   -r FILENAME, --root=FILENAME
 *                         add root element
 *   -t FILENAME, --task=FILENAME
 *                         set task
 *
 *   Per-flight options:
 *     -i FILENAME, --igc=FILENAME
 *                         set flight IGC file
 *     -n STRING, --pilot-name=STRING
 *                         set pilot name
 *     -g STRING, --glider-type=STRING
 *                         set glider type
 *     -c COLOR, --color=COLOR
 *                         set track line color
 *     -w INTEGER, --width=INTEGER
 *                         set track line width
 *     -u URL, --url=URL   set flight URL
 *     -x FILENAME, --xc=FILENAME
 *                         set flight XC
 *
 *   Per-photo options:
 *     -p URL, --photo=URL
 *                         add photo
 *     -d STRING, --description=STRING
 *                         set photo comment
 * ```
 *
 * @author    Michael Schmidt-Voigt
 * @since     2018-05-09
 * @copyright 2018 (c) Michael Schmidt-Voigt
 * @package   Igc2KmzPhp
 */

namespace M165437\Igc2KmzPhp;

use Symfony\Component\Process\Process;

class Igc2Kmz implements Igc2KmzInterface
{
    /**
     * @var string
     */
    private $binary;

    /**
     * Collect options, e.g. output, format etc.
     *
     * Format:
     *   '--option-name' => 'option value'
     *   '--output'      => 'path/to/output/file'
     *
     * @var mixed[]
     */
    private $options = [];

    /**
     * Collect photos
     *
     * Format:
     *   [
     *     'url' => 'https://domain.tld/photo.jpg',
     *     'description' => 'my comment'
     *   ]
     *
     * @var array[]
     */
    private $photos = [];

    /**
     * Create an igc2kmz wrapper by passing the path to your igc2kmz bin.
     *
     * Hint:
     *  You can usually reflect this with your dependency injection container.
     *
     * @param string $binPath Path to the igc2kmz binary
     */
    public function __construct($binPath)
    {
        $this->binary = $binPath;
    }

    public function output($path)
    {
        $this->options['--output'] = $path;

        return $this;
    }

    public function tzOffset($hours)
    {
        $this->options['--tz-offset'] = $hours;

        return $this;
    }

    public function root($path)
    {
        $this->options['--root'] = $path;

        return $this;
    }

    public function task($path)
    {
        $this->options['--task'] = $path;

        return $this;
    }

    public function igc($path)
    {
        $this->options['--igc'] = $path;

        return $this;
    }

    public function pilotName($name)
    {
        $this->options['--pilot-name'] = $name;

        return $this;
    }

    public function gliderType($type)
    {
        $this->options['--glider-type'] = $type;

        return $this;
    }

    public function color($color)
    {
        $this->options['--color'] = $color;

        return $this;
    }

    public function width($width)
    {
        $this->options['--width'] = $width;

        return $this;
    }

    public function url($url)
    {
        $this->options['--url'] = $url;

        return $this;
    }

    public function xc($path)
    {
        $this->options['--xc'] = $path;

        return $this;
    }

    public function addPhoto($url, $description = '')
    {
        array_push(
            $this->photos,
            ['url' => $url, 'description' => $description]
        );

        return $this;
    }

    public function getPhotos()
    {
        return $this->photos;
    }

    public function resetPhotos()
    {
        $this->photos = [];

        return $this;
    }

    public function build()
    {
        $this->validateOptionsAndArguments();

        $process = new Process(
            $this->getProcessCommand()
        );

        return $process;
    }

    public function run(Process $process = null)
    {
        if (null === $process) {
            $process = $this->build();
        }

        $process->run();

        if ($process->getExitCode() !== 0) {
            throw new \Exception($process->getErrorOutput());
        }

        return $process->getOutput();
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function resetOptions()
    {
        $this->options = [];

        return $this;
    }

    public function getBinary()
    {
        return $this->binary;
    }

    public function setBinary($binary)
    {
        $this->binary = $binary;

        return $this;
    }

    /**
     * Check the configuration of the object prior to executing the process.
     *
     * igc2kmz must not be called without an igc argument.
     *
     * @throws \Exception
     */
    private function validateOptionsAndArguments()
    {
        if (! array_key_exists('--igc', $this->options)) {
            throw new \Exception("IGC argument missing");
        }
    }

    /**
     * Get command to pass it into the process.
     *
     * @return string
     */
    private function getProcessCommand()
    {
        $options = $this->transformOptions();

        $command = $this->binary . ' ' . implode(' ', $options);

        return $command;
    }

    /**
     * Return options prepared to be passed into the Process.
     *
     * E.g.: ["--output" => "path/to/file"] -> ["--output=path/to/file"]
     *
     * The original format is an associative array, where the key is the
     * option name and the value is the respective value.
     * The process will want those as single strings to escape them
     * for the command line. Hence, we have to turn ["--output" => "path/to/file"]
     * into ["--output=path/to/file"].
     *
     * @return \string[]
     */
    private function transformOptions()
    {
        $processOptions = [];

        foreach ($this->options as $key => $value) {
            $option = $key;

            if ($value) {
                $option .= '=' . $value;
            }

            $processOptions[] = $option;
        }

        foreach ($this->photos as $photo) {
            $option = '--photo=' . $photo['url'];

            if (! empty($photo['description'])) {
                $option .= ' --description="' . $photo['description'] . '"';
            }

            $processOptions[] = $option;
        }

        return $processOptions;
    }
}
