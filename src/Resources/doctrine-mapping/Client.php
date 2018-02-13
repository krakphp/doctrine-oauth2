<?php

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Krak\DoctrineOAuth2\Model\{Client, Scope};

$builder = new ClassMetadataBuilder($metadata);
$builder->setTable('oauth2_clients');
$builder->createField('id', 'string')->isPrimaryKey()->build();
$builder->addField('secret', 'string');
$builder->addField('name', 'string');
$builder->addField('redirectUri', 'string');
$builder->addField('createdAt', 'datetime');

$builder->createManyToMany('scopes', Scope::class)
    ->setJoinTable('oauth2_clients_scopes')
    ->addJoinColumn('client_id', 'id')
    ->addInverseJoinColumn('scope_id', 'id')
    ->build();
