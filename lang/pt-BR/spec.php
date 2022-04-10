<?php
/*
 * Copyright 2022 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | JSON:API Specification Errors
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default translatable members
    | of JSON:API error objects. According to the JSON:API spec, the
    | `title` and `detail` members can be localized. In addition the `code`
    | member is also read from this package if you want to give the error
    | a specific code.
    |
    | Set any value to an empty string if you do not want the member to be
    | included in the error object.
    |
    | @see http://jsonapi.org/format/#errors
    */

    'json_empty' => [
        'title' => 'JSON inválido',
        'detail' => 'Esperava-se JSON para se descodificar.',
        'código' => '',
    ],

    'json_error' => [
        'title' => 'JSON inválido',
    ],

    'json_not_object' => [
        'title' => 'JSON inválido',
        'detail' => 'Esperava-se um JSON para se descodificar num objeto.',
        'código' => '',
    ],

    'membro_requerido' => [
        'title' => 'Documento JSON:API não compatível',
        'detail' => 'O membro :member é obrigatório.',
        'código' => '',
    ],

    'member_object_expected' => [
        'title' => 'Documento JSON:API não compatível',
        'detail' => 'O membro :member deve ser um objeto.',
        'código' => '',
    ],

    'member_array_expected' => [
        'title' => 'Documento JSON: API não compatível',
        'detail' => 'O membro :member deve ser um array.',
        'código' => '',
    ],

    'membro_identificador_esperado' => [
        'title' => 'Documento JSON:API não compatível',
        'detail' => 'O membro :member deve ser um identificador do recurso.',
        'código' => '',
    ],

    'member_string_expected' => [
        'title' => 'Documento JSON:API não compatível',
        'detail' => 'O membro :member deve ser uma string.',
        'código' => '',
    ],

    'membro_vazio' => [
        'title' => 'Documento JSON:API não compatível',
        'detail' => 'O membro :member não pode estar vazio.',
        'código' => '',
    ],

    'member_field_not_allowed' => [
        'title' => 'Documento JSON:API não compatível',
        'detail' => 'O membro :member não pode ter um campo :field.',
        'código' => '',
    ],

    'member_field_not_supported' => [
        'title' => 'Documento JSON:API não compatível',
        'detail' => 'O campo :field não é do tipo :type suportado.',
        'código' => '',
    ],

    'field_expects_to_one' => [
        'title' => 'Documento JSON:API não compatível',
        'detail' => 'O campo :field deve ser uma relação do tipo para-um.',
        'código' => '',
    ],

    'field_expects_to_many' => [
        'title' => 'Documento JSON: API não compatível',
        'detail' => 'O campo :field deve ser uma relação do tipo para-muitos.',
        'código' => '',
    ],

    'resource_type_not_supported' => [
        'title' => 'Não suportado',
        'detail' => 'O tipo de recurso :type não é suportado por este endpoint.',
        'código' => '',
    ],

    'resource_type_not_supported_by_to_one_relationship' => [
        'title' => 'Entidade não processável',
        'detail' => 'O campo :field deve ser uma relação do tipo para-um contendo recursos :types.',
        'código' => '',
    ],

    'resource_type_not_supported_by_to_many_relationship' => [
        'title' => 'Entidade não processável',
        'detail' => 'O: campo deve ser uuma relação do tipo para-muitos contendo recursos :types.',
        'código' => '',
    ],

    'resource_type_not_recognised' => [
        'title' => 'Não suportado',
        'detail' => 'O tipo de recurso :type não é reconhecido.',
        'código' => '',
    ],

    'resource_id_not_supported' => [
        'title' => 'Não suportado',
        'detail' => 'O ID do recurso :id não é suportado por este endpoint.',
        'código' => '',
    ],

    'resource_client_ids_not_supported' => [
        'title' => 'Não suportado',
        'detail' => 'O tipo de recurso :type não suporta IDs gerados pelo cliente.',
        'código' => '',
    ],

    'resource_exists' => [
        'title' => 'Conflito',
        'detail' => 'O recurso :id já existe.',
        'código' => '',
    ],

    'resource_not_found' => [
        'title' => 'Não encontrado',
        'detail' => 'O recurso relacionado não existe.',
        'código' => '',
    ],

    'resource_field_exists_in_attributes_and_relationships' => [
        'title' => 'Documento JSON:API não compatível',
        'detail' => 'O campo :field não pode existir como atributo e como relação.',
        'código' => '',
    ],
];
