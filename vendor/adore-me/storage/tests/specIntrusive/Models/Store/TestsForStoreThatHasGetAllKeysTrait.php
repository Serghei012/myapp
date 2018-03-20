<?php
namespace specIntrusive\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use PhpSpec\Wrapper\Subject;

trait TestsForStoreThatHasGetAllKeysTrait
{
    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getNamespaceTags_should_work()
    {
        /** @var StoreInterface $this */
        /** @var Subject|string $oldNamespace */
        $oldNamespace = $this->getPrefix();
        $oldNamespace = $oldNamespace->getWrappedObject();
        $newNamespace = 'tests 2';

        $this->forever('test_key', 'test_value');
        $this->forever('test_key2', 'test_value2', ['tag 1', 'tag 2']);
        $this->forever('test_key3', 'test_value3', ['tag 2', 'tag 3']);
        $this->forget('test_key2');

        // Change namespace via reflection.
        $this->specChangeNamespaceViaReflection($newNamespace);

        $this->forever('test_key', 'test_value');
        $this->forever('test_key2', 'test_value2', ['tag 1', 'tag 2']);
        $this->forever('test_key3', 'test_value3', ['tag 2', 'tag 3']);
        $this->forget('test_key');

        // Change namespace via reflection.
        $this->specChangeNamespaceViaReflection($oldNamespace);

        /** @var Subject $result */
        /** @noinspection PhpInternalEntityUsedInspection */
        $result = $this->getNamespaceTags(true);
        $result->shouldNotContain('tag 1');
        $result->shouldContain('tag 2');
        $result->shouldContain('tag 3');

        // Change namespace via reflection.
        $this->specChangeNamespaceViaReflection($newNamespace);

        /** @var Subject $result */
        /** @noinspection PhpInternalEntityUsedInspection */
        $result = $this->getNamespaceTags(true);
        $result->shouldContain('tag 1');
        $result->shouldContain('tag 2');
        $result->shouldContain('tag 3');

        // Reset namespace to default.
        $this->specChangeNamespaceViaReflection($oldNamespace);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getAllTags_should_work()
    {
        /** @var StoreInterface $this */
        $this->forever('test_key', 'test_value');
        $this->forever('test_key2', 'test_value2', ['tag 1', 'tag 2']);
        $this->forever('test_key3', 'test_value2', ['tag 2', 'tag 3']);
        $this->forget('test_key2');

        /** @var Subject|string $tag1 */
        $tag1 = $this->prepareTag('tag 1');
        $tag1 = $tag1->getWrappedObject();

        /** @var Subject|string $tag2 */
        $tag2 = $this->prepareTag('tag 2');
        $tag2 = $tag2->getWrappedObject();

        /** @var Subject|string $tag3 */
        $tag3 = $this->prepareTag('tag 3');
        $tag3 = $tag3->getWrappedObject();

        /** @var Subject $result */
        /** @noinspection PhpInternalEntityUsedInspection */
        $result = $this->getAllTags();
        $result->shouldNotContain($tag1);
        $result->shouldContain($tag2);
        $result->shouldContain($tag3);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getNamespaceKeys_should_work()
    {
        /** @var StoreInterface $this */
        /** @var Subject|string $oldNamespace */
        $oldNamespace = $this->getPrefix();
        $oldNamespace = $oldNamespace->getWrappedObject();
        $newNamespace = $this->specNewNamespace;

        $this->forever('test_key', 'test_value');
        $this->forever('test_key2', 'test_value2');
        $this->forever('test_key3', 'test_value3');
        $this->forget('test_key2');

        // Change namespace via reflection.
        $this->specChangeNamespaceViaReflection($newNamespace);

        $this->forever('test_key', 'test_value');
        $this->forever('test_key2', 'test_value2');
        $this->forever('test_key3', 'test_value3');
        $this->forget('test_key');

        // Change namespace via reflection.
        $this->specChangeNamespaceViaReflection($oldNamespace);

        /** @var Subject $result */
        /** @noinspection PhpInternalEntityUsedInspection */
        $result = $this->getNamespaceKeys(true);
        $result->shouldContain('test_key');
        $result->shouldNotContain('test_key2');
        $result->shouldContain('test_key3');

        // Change namespace via reflection.
        $this->specChangeNamespaceViaReflection($newNamespace);

        /** @var Subject $result */
        /** @noinspection PhpInternalEntityUsedInspection */
        $result = $this->getNamespaceKeys(true);
        $result->shouldNotContain('test_key');
        $result->shouldContain('test_key2');
        $result->shouldContain('test_key3');

        // Reset namespace to default.
        $this->specChangeNamespaceViaReflection($oldNamespace);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getAllKeys_should_work()
    {
        /** @var StoreInterface $this */
        $this->forever('test_key', 'test_value');
        $this->forever('test_key2', 'test_value2');
        $this->forever('test_key3', 'test_value2');

        $this->forget('test_key2');

        /** @var Subject $result */
        /** @noinspection PhpInternalEntityUsedInspection */
        $result = $this->getAllKeys();

        /** @var Subject|string $key1 */
        $key1 = $this->prepareKey('test_key');
        $key1 = $key1->getWrappedObject();

        /** @var Subject|string $key2 */
        $key2 = $this->prepareKey('test_key2');
        $key2 = $key2->getWrappedObject();

        /** @var Subject|string $key3 */
        $key3 = $this->prepareKey('test_key3');
        $key3 = $key3->getWrappedObject();

        $result->shouldContain($key1);
        $result->shouldNotContain($key2);
        $result->shouldContain($key3);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_flushAll_will_remove_all_keys_from_store()
    {
        /** @var StoreInterface $this */
        $this->forever('test_key', 'test_value');
        $this->forever('test_key2', 'test_value2');
        $this->forever('test_key3', 'test_value3');

        $this->flushAll();

        /** @var Subject $result */
        /** @noinspection PhpInternalEntityUsedInspection */
        $result = $this->getAllKeys();
        $result->shouldIterateAs([]);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_flushNamespace_will_remove_only_keys_from_current_namespace()
    {
        /** @var StoreInterface $this */
        /** @var Subject|string $oldNamespace */
        $oldNamespace = $this->getPrefix();
        $oldNamespace = $oldNamespace->getWrappedObject();
        $newNamespace = $this->specNewNamespace;

        $this->forever('test_key', 'test_value');
        $this->forever('test_key2', 'test_value2');
        $this->forever('test_key3', 'test_value3');

        // Change namespace via reflection.
        $this->specChangeNamespaceViaReflection($newNamespace);

        $this->forever('test_key', 'test_value');
        $this->forever('test_key2', 'test_value2');
        $this->forever('test_key3', 'test_value3');

        // Change namespace via reflection.
        $this->specChangeNamespaceViaReflection($oldNamespace);

        $this->flushNamespace();

        // Verify that we still have the keys from new namespace.

        /** @var Subject $result */
        /** @noinspection PhpInternalEntityUsedInspection */
        $result = $this->getNamespaceKeys();
        $result->shouldIterateAs([]);

        // Change namespace via reflection.
        $this->specChangeNamespaceViaReflection($newNamespace);

        /** @var Subject $result */
        /** @noinspection PhpInternalEntityUsedInspection */
        $result = $this->getNamespaceKeys(true);
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldContainArrayValues(
            [
                'test_key',
                'test_key2',
                'test_key3',
            ]
        );

        // Reset namespace to default.
        $this->specChangeNamespaceViaReflection($oldNamespace);
    }
}
