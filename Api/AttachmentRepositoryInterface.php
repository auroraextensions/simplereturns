<?php
/**
 * AttachmentRepositoryInterface.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package        AuroraExtensions_SimpleReturns
 * @copyright      Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license        MIT License
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Api;

interface AttachmentRepositoryInterface extends AbstractRepositoryInterface
{
    /**
     * @param string $token
     * @return \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(string $token): Data\AttachmentInterface;

    /**
     * @param int $id
     * @return \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): Data\AttachmentInterface;

    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface $attachment
     * @return int
     */
    public function save(Data\AttachmentInterface $attachment): int;

    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface $attachment
     * @return bool
     */
    public function delete(Data\AttachmentInterface $attachment): bool;

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool;
}
