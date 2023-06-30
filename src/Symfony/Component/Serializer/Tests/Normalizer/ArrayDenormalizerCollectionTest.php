<?php
namespace Symfony\Component\Serializer\Tests\Normalizer;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ArrayDenormalizerCollectionTest extends TestCase
{

    public function testDoctrineCollection()
    {
        // Setting up the basic object denormalizer configuration
        $classMetaDataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $discriminator = new ClassDiscriminatorFromClassMetadata($classMetaDataFactory);

        $serializer = new Serializer(
            [
                new ArrayDenormalizer(),
                new ObjectNormalizer($classMetaDataFactory, null, null, new PhpDocExtractor(), $discriminator),
            ],
            [new JsonEncoder()]
        );

        $result = $serializer->deserialize(
            json_encode([
                'baz' => [
                    ['foo' => 'one', 'bar' => 'two'],
                ],
            ]),
            __NAMESPACE__ . '\DummyWithArrayCollection',
            'json'
        );

        $this->assertEquals(
            new DummyWithArrayCollection([new CollectionContentDummy('one', 'two')]),
            $result
        );
    }
}

class CollectionContentDummy
{
    public $foo;
    public $bar;

    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}

class DummyWithArrayCollection
{
    /**
     * @var ArrayCollection<CollectionContentDummy>
     */
    public $baz;

    /**
     * @param array<CollectionContentDummy>
     */
    public function __construct(array $baz)
    {
        $this->baz = new ArrayCollection($baz);
    }
}
