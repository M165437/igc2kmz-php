<?php
/**
 * Test igc2kmz binary wrapper.
 *
 * @author    Michael Schmidt-Voigt
 * @since     2018-05-09
 * @copyright 2018 (c) Michael Schmidt-Voigt
 * @package   Igc2KmzPhp
 */

namespace M165437\Igc2KmzPhp\Tests;

use DOMDocument;
use DOMXPath;
use M165437\Igc2KmzPhp\Igc2Kmz;
use M165437\Igc2KmzPhp\Igc2KmzInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use ZipArchive;

class Igc2KmzTest extends TestCase
{
    /**
     * Path to the igc2kmz bin for the tests.
     * @var string
     */
    private $binPath;

    /**
     * Base path for igc fixtures for the tests.
     * @var string
     */
    private $fixturePath;

    /**
     * Default directory path used for temporary files.
     * @var string
     */
    private $tmpPath;

    /**
     * Igc2Kmz php instance.
     * @var Igc2KmzInterface
     */
    private $igc2kmz;

    /**
     * Init test case.
     */
    protected function setUp()
    {
        $this->binPath     = __DIR__ . '/../vendor/bin/igc2kmz';
        $this->fixturePath = __DIR__ . '/fixtures/';
        $this->tmpPath     = sys_get_temp_dir();
        $this->igc2kmz     = new Igc2Kmz($this->binPath);
    }

    /**
     * Tear down test case.
     */
    public function tearDown()
    {
        if (file_exists($this->tmpPath . 'output.kmz')) {
            unlink($this->tmpPath . 'output.kmz');
        }
    }

    /**
     * @test Create an igc2kmz instance; check if it worked.
     */
    public function create_wrapper_object()
    {
        $this->assertInstanceOf(Igc2Kmz::class, $this->igc2kmz);
    }

    /**
     * @test Configuring the path to igc2kmz binary.
     */
    public function setting_and_getting_the_binary_path()
    {
        $this->assertEquals($this->binPath, $this->igc2kmz->getBinary());
        $this->igc2kmz->setBinary('new.path');
        $this->assertEquals('new.path', $this->igc2kmz->getBinary());
    }

    /**
     * @test Converts igc to kmz.
     * @returns Igc2Kmz $igc2kmz
     * @throws \Exception
     */
    public function converts_igc_to_kmz()
    {
        $this
            ->igc2kmz
            ->igc($this->fixturePath . 'input.igc')
            ->output($this->tmpPath . 'output.kmz')
            ->run();

        $this->assertFileExists($this->tmpPath . 'output.kmz');
        $this->assertXmlStringEqualsXmlString(
            $this->getXmlFromKmz($this->fixturePath . 'reference.kmz'),
            $this->getXmlFromKmz($this->tmpPath . 'output.kmz')
        );

        return $this->igc2kmz;
    }

    private function getXmlFromKmz($path)
    {
        $zip = new ZipArchive;
        $zip->open($path);

        $dom = new DOMDocument;
        $dom->loadXML($zip->getFromName('doc.kml'));

        $xp  = new DOMXPath($dom);
        $xp->registerNamespace('x', 'http://earth.google.com/kml/2.2');

        // Remove unique ids from style nodes
        $nodes = $xp->query('//x:Style');
        foreach ($nodes as $node) {
            $node->removeAttribute('id');
        }

        // Remove unique ids from styleUrl nodes
        $nodes = $xp->query('//x:styleUrl');
        foreach ($nodes as $node) {
            $node->nodeValue = '';
        }

        return $dom->saveXML();
    }

    /**
     * @test The igc2kmz instance will hold its state; we can invoke `run` again for the same result.
     *
     * @param Igc2Kmz $igc2kmz
     * @throws \Exception
     * @depends converts_igc_to_kmz
     */
    public function running_igc2kmz_again_without_reset(Igc2Kmz $igc2kmz)
    {
        $igc2kmz->run();

        $this->assertFileExists($this->tmpPath . 'output.kmz');
    }

    /**
     * @test Build the process instance using igc2kmz and pass it to the run method.
     * @throws \Exception
     */
    public function build_process_and_pass_it_to_run_method()
    {
        $process = $this
            ->igc2kmz
            ->igc($this->fixturePath . 'input.igc')
            ->output($this->tmpPath . 'output.kmz')
            ->build();

        $this->assertInstanceOf(Process::class, $process);

        $this
            ->igc2kmz
            ->run($process);

        $this->assertFileExists($this->tmpPath . 'output.kmz');
    }

    /**
     * @test Setting all supported options with correct syntax.
     */
    public function set_all_available_options()
    {
        $this
            ->igc2kmz
            ->igc('some-input')
            ->output('some-output')
            ->tzOffset(5)
            ->root('some-root')
            ->task('some-task')
            ->pilotName('some-name')
            ->gliderType('some-glider')
            ->color('some-color')
            ->width(8)
            ->url('some-url')
            ->xc('some-xc');

        $expectedOptions = [
            '--igc' => 'some-input',
            '--output' => 'some-output',
            '--tz-offset' => 5,
            '--root' => 'some-root',
            '--task' => 'some-task',
            '--pilot-name' => 'some-name',
            '--glider-type' => 'some-glider',
            '--color' => 'some-color',
            '--width' => 8,
            '--url' => 'some-url',
            '--xc' => 'some-xc'
        ];

        $options = $this->igc2kmz->getOptions();

        $this->assertEquals($expectedOptions, $options);
    }

    /** @test */
    public function add_multiple_photos()
    {
        $this
            ->igc2kmz
            ->addPhoto('some-photo-1', 'some-comment-1')
            ->addPhoto('some-photo-2', 'some-comment-2');

        $expectedPhotos = [
            [ 'url' => 'some-photo-1', 'description' => 'some-comment-1' ],
            [ 'url' => 'some-photo-2', 'description' => 'some-comment-2' ]
        ];

        $photos = $this->igc2kmz->getPhotos();

        $this->assertEquals($expectedPhotos, $photos);
    }

    /** @test */
    public function options_and_multiple_photos_as_command_line()
    {
        $process = $this
            ->igc2kmz
            ->igc($this->fixturePath . 'input.igc')
            ->output($this->tmpPath . 'output.kmz')
            ->addPhoto('url/photo/1', 'my comment 1')
            ->addPhoto('url/photo/2')
            ->build();

        $expectedCommandLine = sprintf(
            '%s --igc=%s --output=%s --photo=%s --description="%s" --photo=%s',
            $this->binPath,
            $this->fixturePath . 'input.igc',
            $this->tmpPath . 'output.kmz',
            'url/photo/1',
            'my comment 1',
            'url/photo/2'
        );

        $this->assertEquals($expectedCommandLine, $process->getCommandLine());
    }

    /**
     * @test Resetting all options on the instance.
     */
    public function reset_options()
    {
        $this
            ->igc2kmz
            ->igc('some-input');

        $this->assertCount(1, $this->igc2kmz->getOptions());

        $this
            ->igc2kmz
            ->resetOptions();

        $this->assertCount(0, $this->igc2kmz->getOptions());
    }

    /**
     * @test Catch an exception if the binary cannot be found.
     *
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /INVALID/
     */
    public function invalid_binary_will_throw_exception()
    {
        $this
            ->igc2kmz
            ->setBinary('INVALID')
            ->igc('some-input')
            ->run();
    }

    /**
     * @test Catch an exception if the input file cannot be opened.
     *
     * @expectedException \Exception
     * @expectedExceptionMessage RuntimeError: unsupported file type
     */
    public function invalid_igc_argument_will_throw_exception()
    {
        $this
            ->igc2kmz
            ->igc('INVALID')
            ->run();
    }

    /**
     * @test Catch an exception if no option or argument is set.
     *
     * @expectedException \Exception
     * @expectedExceptionMessage IGC argument missing
     */
    public function exception_when_igc_argument_is_missing()
    {
        $this->igc2kmz->run();
    }
}
