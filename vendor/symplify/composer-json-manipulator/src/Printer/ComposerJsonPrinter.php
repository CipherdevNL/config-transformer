<?php

declare (strict_types=1);
namespace ConfigTransformer20210610\Symplify\ComposerJsonManipulator\Printer;

use ConfigTransformer20210610\Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager;
use ConfigTransformer20210610\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use ConfigTransformer20210610\Symplify\SmartFileSystem\SmartFileInfo;
final class ComposerJsonPrinter
{
    /**
     * @var JsonFileManager
     */
    private $jsonFileManager;
    public function __construct(\ConfigTransformer20210610\Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager $jsonFileManager)
    {
        $this->jsonFileManager = $jsonFileManager;
    }
    public function printToString(\ConfigTransformer20210610\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson $composerJson) : string
    {
        return $this->jsonFileManager->encodeJsonToFileContent($composerJson->getJsonArray());
    }
    /**
     * @param string|SmartFileInfo $targetFile
     */
    public function print(\ConfigTransformer20210610\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson $composerJson, $targetFile) : string
    {
        if (\is_string($targetFile)) {
            return $this->jsonFileManager->printComposerJsonToFilePath($composerJson, $targetFile);
        }
        return $this->jsonFileManager->printJsonToFileInfo($composerJson->getJsonArray(), $targetFile);
    }
}
