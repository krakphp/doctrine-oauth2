<?php

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Krak\DoctrineOAuth2\Model\{Client, Scope};

$builder = new ClassMetadataBuilder($metadata);
$builder->setTable('oauth2_access_tokens');
$builder->createField('id', 'string')->isPrimaryKey()->build();
$builder->addField('expiryDateTime', 'datetime');
$builder->createField('userId', 'string')->nullable()->build();
$builder->addField('isRevoked', 'boolean');
$builder->addField('createdAt', 'datetime');

$builder->createManyToOne('client', Client::class)->addJoinColumn('client_id', 'id')->build();
$builder->createManyToMany('scopes', Scope::class)
    ->setJoinTable('oauth2_access_tokens_scopes')
    ->addJoinColumn('access_token_id', 'id')
    ->addInverseJoinColumn('scope_id', 'id')
    ->build();
