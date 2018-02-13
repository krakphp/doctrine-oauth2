<?php

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Krak\DoctrineOAuth2\Model\{AccessToken};

$builder = new ClassMetadataBuilder($metadata);
$builder->setTable('oauth2_refresh_tokens');
$builder->createField('id', 'string')->isPrimaryKey()->build();
$builder->addField('expiryDateTime', 'datetime');
$builder->addField('isRevoked', 'boolean');
$builder->addField('createdAt', 'datetime');

$builder->createOneToOne('accessToken', AccessToken::class)
    ->addJoinColumn('access_token_id', 'id')
    ->build();
