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
        'title' => 'JSON no válido',
        'detail' => 'Se esperaba un JSON para ser decodificado.',
        'code' => '',
    ],

    'json_error' => [
        'title' => 'JSON no válido',
    ],

    'json_not_object' => [
        'title' => 'JSON no válido',
        'detail' => 'Se esperaba un JSON para ser decodificado en un objeto.',
        'code' => '',
    ],

    'member_required' => [
        'title' => 'Documento JSON:API no satisface los requerimientos',
        'detail' => 'El miembro :member es requerido.',
        'code' => '',
    ],

    'member_object_expected' => [
        'title' => 'Documento JSON:API no satisface los requerimientos',
        'detail' => 'El miembro :member debe ser un objeto.',
        'code' => '',
    ],

    'member_array_expected' => [
        'title' => 'Documento JSON:API no satisface los requerimientos',
        'detail' => 'El miembro :member debe ser un arreglo.',
        'code' => '',
    ],

    'member_identifier_expected' => [
        'title' => 'Documento JSON:API no satisface los requerimientos',
        'detail' => 'El miembro :member debe ser un identificador del recurso.',
        'code' => '',
    ],

    'member_string_expected' => [
        'title' => 'Documento JSON:API no satisface los requerimientos',
        'detail' => 'El miembro :member debe ser una cadena de caracteres.',
        'code' => '',
    ],

    'member_empty' => [
        'title' => 'Documento JSON:API no satisface los requerimientos',
        'detail' => 'El miembro :member no puede estar vacio.',
        'code' => '',
    ],

    'member_field_not_allowed' => [
        'title' => 'Documento JSON:API no satisface los requerimientos',
        'detail' => 'El miembro :member no puede tener un :field campo.',
        'code' => '',
    ],

    'member_field_not_supported' => [
        'title' => 'Documento JSON:API no satisface los requerimientos',
        'detail' => ' El campo :field no es del tipo :type soportado.',
        'code' => '',
    ],

    'field_expects_to_one' => [
        'title' => 'Documento JSON:API no satisface los requerimientos',
        'detail' => ' El campo :field debe ser una relación a-uno.',
        'code' => '',
    ],

    'field_expects_to_many' => [
        'title' => 'Documento JSON:API no satisface los requerimientos',
        'detail' => ' El campo :field debe ser una relación a-muchos.',
        'code' => '',
    ],

    'resource_type_not_supported' => [
        'title' => 'No Soportado',
        'detail' => 'Recurso del tipo :type no es soportado por este endpoint.',
        'code' => '',
    ],

    'resource_type_not_supported_by_to_one_relationship' => [
        'title' => 'Entidad No Procesable',
        'detail' => 'El :field campo debe ser una relación a-uno que contenga recursos :types.',
        'code' => '',
    ],

    'resource_type_not_supported_by_to_many_relationship' => [
        'title' => 'Entidad No Procesable',
        'detail' => 'El :field campo debe ser una relación a-muchos que contenga recursos :types.',
        'code' => '',
    ],

    'resource_type_not_recognised' => [
        'title' => 'No Soportado',
        'detail' => 'Recurso del tipo :type no es reconocido.',
        'code' => '',
    ],

    'resource_id_not_supported' => [
        'title' => 'No Soportado',
        'detail' => 'Id del recurso :id no es soportado por este endpoint.',
        'code' => '',
    ],

    'resource_client_ids_not_supported' => [
        'title' => 'No Soportado',
        'detail' => 'Recurso del tipo :type no admite IDs generadas por el cliente.',
        'code' => '',
    ],

    'resource_exists' => [
        'title' => 'Conflicto',
        'detail' => 'Recurso :id ya existe.',
        'code' => '',
    ],

    'resource_not_found' => [
        'title' => 'No Encontrado',
        'detail' => 'El recurso relacionado no existe.',
        'code' => '',
    ],

    'resource_field_exists_in_attributes_and_relationships' => [
        'title' => 'Documento JSON:API no satisface los requerimientos',
        'detail' => 'El :field campo no puede existir como un atributo y una relación a la vez.',
        'code' => '',
    ],
];
