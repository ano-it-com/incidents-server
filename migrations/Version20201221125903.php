<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201221125903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление регионов';
    }

    public function up(Schema $schema): void
    {
        $idsMapping = [];
        $id = 0;
        foreach ($this->getRegions() as $region) {
            $this->connection->insert('locations', [
                'id' => ++$id,
                'title' => $region['region'],
                'level' => $region['level'] - 2,
                'parent_id' => $region['parent_id'] != null ? $idsMapping[$region['parent_id']] : null
            ]);
            $idsMapping[$region['id']] = $id;
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('TRUNCATE TABLE locations');
    }

    protected function getRegions()
    {
        return [
            ['id' => 'd9b590f8-6c96-40e9-9226-db3d0dc80a96', 'region' => 'Россия', 'parent_id' => null, 'level' => 2],
            ['id' => 'b8c1ebb7-3d34-4ac8-8e1c-40915392cd49', 'region' => 'Сибирский федеральный округ', 'parent_id' => 'd9b590f8-6c96-40e9-9226-db3d0dc80a96', 'level' => 3],
            ['id' => 'a8511d70-9a5b-4d88-9713-ad0e8c07ec14', 'region' => 'Дальневосточный федеральный округ', 'parent_id' => 'd9b590f8-6c96-40e9-9226-db3d0dc80a96', 'level' => 3],
            ['id' => 'c0cc6248-839a-4694-ad93-da6426a392b1', 'region' => 'Уральский федеральный округ', 'parent_id' => 'd9b590f8-6c96-40e9-9226-db3d0dc80a96', 'level' => 3],
            ['id' => 'ad871288-3201-4c87-867d-044afdce39b6', 'region' => 'Южный федеральный округ', 'parent_id' => 'd9b590f8-6c96-40e9-9226-db3d0dc80a96', 'level' => 3],
            ['id' => '7e82c216-c3d3-476d-8538-bde6b933a374', 'region' => 'Северо-Кавказский федеральный округ', 'parent_id' => 'd9b590f8-6c96-40e9-9226-db3d0dc80a96', 'level' => 3],
            ['id' => '6bc63d3e-acbb-4728-82d2-4819886c9078', 'region' => 'Северо-Западный федеральный округ', 'parent_id' => 'd9b590f8-6c96-40e9-9226-db3d0dc80a96', 'level' => 3],
            ['id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'region' => 'Приволжский федеральный округ', 'parent_id' => 'd9b590f8-6c96-40e9-9226-db3d0dc80a96', 'level' => 3],
            ['id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'region' => 'Центральный федеральный округ', 'parent_id' => 'd9b590f8-6c96-40e9-9226-db3d0dc80a96', 'level' => 3],
            ['id' => '7dff48e5-28df-4e74-afc8-d8b54080570b', 'region' => 'Ленинградская область', 'parent_id' => '6bc63d3e-acbb-4728-82d2-4819886c9078', 'level' => 4],
            ['id' => 'b6654226-dfd1-4122-b027-7a2894877a4c', 'region' => 'Ставропольский край', 'parent_id' => '7e82c216-c3d3-476d-8538-bde6b933a374', 'level' => 4],
            ['id' => '9e59a1ec-5537-4f9c-b72a-86107d0c567c', 'region' => 'Рязанская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => '73e8291e-d665-4836-b90e-5635e8ea51a0', 'region' => 'Архангельская область', 'parent_id' => '6bc63d3e-acbb-4728-82d2-4819886c9078', 'level' => 4],
            ['id' => '06b11556-ea4f-46bf-8307-08c683708de2', 'region' => 'Курская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => '73585770-e6c0-4485-8fcc-b9737f63d663', 'region' => 'Москва', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => 'c4e04128-8eed-45c7-9fa2-1bb08c6f1f9d', 'region' => 'Омская область', 'parent_id' => 'b8c1ebb7-3d34-4ac8-8e1c-40915392cd49', 'level' => 4],
            ['id' => 'e7454486-76de-42ca-aa62-b751615ba8db', 'region' => 'Волгоградская область', 'parent_id' => 'ad871288-3201-4c87-867d-044afdce39b6', 'level' => 4],
            ['id' => '1f331698-a632-452d-a695-2d45c98fa36a', 'region' => 'Республика Дагестан', 'parent_id' => '7e82c216-c3d3-476d-8538-bde6b933a374', 'level' => 4],
            ['id' => '12c54488-bfa2-41ff-8758-05e45cb03bd7', 'region' => 'Еврейская автономная область', 'parent_id' => 'a8511d70-9a5b-4d88-9713-ad0e8c07ec14', 'level' => 4],
            ['id' => 'b1f7cd50-ca52-4fe5-84ed-c0b269a2fe3a', 'region' => 'Башкортостан', 'parent_id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'level' => 4],
            ['id' => '5f4875a3-4ed9-4bbd-95c5-c79a6dce7629', 'region' => 'Псковская область', 'parent_id' => '6bc63d3e-acbb-4728-82d2-4819886c9078', 'level' => 4],
            ['id' => 'b113197f-fa13-4531-8e98-8e228a02b9b6', 'region' => 'Томская область', 'parent_id' => 'b8c1ebb7-3d34-4ac8-8e1c-40915392cd49', 'level' => 4],
            ['id' => 'd02d2f9f-7e87-41e8-98a0-01020f7f041b', 'region' => 'Мордовия', 'parent_id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'level' => 4],
            ['id' => 'a2aec716-a788-45cf-87d9-bffc07996173', 'region' => 'Тверская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => '3e89b571-235f-48ad-9901-e079eb97fe57', 'region' => 'Нижегородская область', 'parent_id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'level' => 4],
            ['id' => '89f5a261-ba03-4bee-bd01-c1b0cba60f78', 'region' => 'Ульяновская область', 'parent_id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'level' => 4],
            ['id' => 'dc2b7063-9f50-4927-94cd-32781957d92f', 'region' => 'Сахалинская область', 'parent_id' => 'a8511d70-9a5b-4d88-9713-ad0e8c07ec14', 'level' => 4],
            ['id' => '8f04029f-06bd-4b11-ac8f-0edbcc5a5f29', 'region' => 'Новгородская область', 'parent_id' => '6bc63d3e-acbb-4728-82d2-4819886c9078', 'level' => 4],
            ['id' => '10455ed7-528d-43a7-ad04-11385dda37a0', 'region' => 'Иркутская область', 'parent_id' => 'b8c1ebb7-3d34-4ac8-8e1c-40915392cd49', 'level' => 4],
            ['id' => '9cc6693f-cf98-403d-9a79-a2247bd4f876', 'region' => 'Марий Эл', 'parent_id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'level' => 4],
            ['id' => '642c7d8c-8558-45fb-bd74-d4f0dd6fd5a8', 'region' => 'Адыгея', 'parent_id' => 'ad871288-3201-4c87-867d-044afdce39b6', 'level' => 4],
            ['id' => 'a44f8154-85fd-4c03-a7ff-a863cbf85ed8', 'region' => 'Калининградская область', 'parent_id' => '6bc63d3e-acbb-4728-82d2-4819886c9078', 'level' => 4],
            ['id' => '942f1fff-e5f1-46a8-be86-53621495e506', 'region' => 'Орловская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => '31d5ba85-20dd-47d1-be48-05318d69cf7e', 'region' => 'Оренбургская область', 'parent_id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'level' => 4],
            ['id' => '667810ea-0a59-40d1-b8b0-5241f93e55b8', 'region' => 'Республика Бурятия', 'parent_id' => 'a8511d70-9a5b-4d88-9713-ad0e8c07ec14', 'level' => 4],
            ['id' => '3a253ac7-23c2-4961-a015-3e673c20b370', 'region' => 'Чувашия', 'parent_id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'level' => 4],
            ['id' => '6daff42e-520c-4dbb-9f78-68ae79078441', 'region' => 'Санкт-Петербург', 'parent_id' => '6bc63d3e-acbb-4728-82d2-4819886c9078', 'level' => 4],
            ['id' => '06308f8c-308b-4ac7-b727-d7c57351a825', 'region' => 'Северная Осетия - Алания', 'parent_id' => '7e82c216-c3d3-476d-8538-bde6b933a374', 'level' => 4],
            ['id' => '941e05a9-6f1e-472e-ac46-f3d0abadca0d', 'region' => 'Карачаево-Черкесия', 'parent_id' => '7e82c216-c3d3-476d-8538-bde6b933a374', 'level' => 4],
            ['id' => 'd962ef0d-7b70-4e5c-a6cc-2ff21af8e75d', 'region' => 'Камчатский край', 'parent_id' => 'a8511d70-9a5b-4d88-9713-ad0e8c07ec14', 'level' => 4],
            ['id' => '12d26afd-8a25-40c8-9d60-0e4486b088c7', 'region' => 'Липецкая область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => '735d8745-29ac-4fec-8b91-e89e4320448b', 'region' => 'Кемеровская область', 'parent_id' => 'b8c1ebb7-3d34-4ac8-8e1c-40915392cd49', 'level' => 4],
            ['id' => 'e415051f-1b11-4ec1-97a8-ce59ecf181cf', 'region' => 'Белгородская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => '65cc1ff9-a118-4f6c-968f-e89ceb6e1714', 'region' => 'Удмуртия', 'parent_id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'level' => 4],
            ['id' => 'de4168b4-a0e9-45de-9a86-51a1912e98da', 'region' => 'Костромская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => 'ad7a4ae3-97ee-47b0-9063-6a3acdba5fab', 'region' => 'Кабардино-Балкария', 'parent_id' => '7e82c216-c3d3-476d-8538-bde6b933a374', 'level' => 4],
            ['id' => '4db00c2b-4108-4821-88a7-d0d5e73779d2', 'region' => 'Республика Крым', 'parent_id' => 'ad871288-3201-4c87-867d-044afdce39b6', 'level' => 4],
            ['id' => '1f7c3657-6926-4292-b11b-1305a9a1a8c6', 'region' => 'Республика Коми', 'parent_id' => '6bc63d3e-acbb-4728-82d2-4819886c9078', 'level' => 4],
            ['id' => '8e98215e-f574-4b8c-8fc1-39a13510ef05', 'region' => 'Свердловская область', 'parent_id' => 'c0cc6248-839a-4694-ad93-da6426a392b1', 'level' => 4],
            ['id' => 'ebad8135-76d0-4e92-9088-c1f495a01b20', 'region' => 'Краснодарский край', 'parent_id' => 'ad871288-3201-4c87-867d-044afdce39b6', 'level' => 4],
            ['id' => '41644e4d-5573-42c2-be1d-1cf78edcbbdd', 'region' => 'Тюменская область', 'parent_id' => 'c0cc6248-839a-4694-ad93-da6426a392b1', 'level' => 4],
            ['id' => 'a217543c-252a-4bea-a0be-fe1bdc5d9a6b', 'region' => 'Курганская область', 'parent_id' => 'c0cc6248-839a-4694-ad93-da6426a392b1', 'level' => 4],
            ['id' => 'e3613dc7-d14e-4d42-b827-ffefa5dc92ae', 'region' => 'Смоленская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => 'fe6ae088-0960-42f2-8119-602b143af3ef', 'region' => 'Брянская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => '8d91a8b4-f834-45bc-82d4-b4b6890cd3c6', 'region' => 'Пермский край', 'parent_id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'level' => 4],
            ['id' => '8d9e05c1-b22f-4d5d-9759-6f917947c0b5', 'region' => 'Республика Тыва', 'parent_id' => 'b8c1ebb7-3d34-4ac8-8e1c-40915392cd49', 'level' => 4],
            ['id' => 'f48549fc-cd1f-47ec-97ba-60ef23df63f6', 'region' => 'Челябинская область', 'parent_id' => 'c0cc6248-839a-4694-ad93-da6426a392b1', 'level' => 4],
            ['id' => 'ee74ab49-a0a3-49b1-96da-68c182827f28', 'region' => 'Ханты-Мансийский автономный округ — Югра', 'parent_id' => 'c0cc6248-839a-4694-ad93-da6426a392b1', 'level' => 4],
            ['id' => '2a419366-8198-436f-967d-54d3f0fb311c', 'region' => 'Тульская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => 'b5c98bf8-ad90-4594-8f82-c200da0cb286', 'region' => 'Республика Алтай', 'parent_id' => 'b8c1ebb7-3d34-4ac8-8e1c-40915392cd49', 'level' => 4],
            ['id' => '6e07e222-439a-46fe-8857-aa541e55bbbb', 'region' => 'Ингушетия', 'parent_id' => '7e82c216-c3d3-476d-8538-bde6b933a374', 'level' => 4],
            ['id' => '53facdf8-44e2-45e6-9404-57297c409031', 'region' => 'Ивановская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => 'ca4c61ac-2468-4f3c-afd0-6241183cae4b', 'region' => 'Магаданская область', 'parent_id' => 'a8511d70-9a5b-4d88-9713-ad0e8c07ec14', 'level' => 4],
            ['id' => '2feaadb3-5fab-47b7-a4de-ecd32b0d7da5', 'region' => 'Кировская область', 'parent_id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'level' => 4],
            ['id' => 'fd43c514-9db0-4ed3-8f32-1105aaa60867', 'region' => 'Пензенская область', 'parent_id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'level' => 4],
            ['id' => 'a2977894-a177-4de8-9d33-d8708aebb3f5', 'region' => 'Красноярский край', 'parent_id' => 'b8c1ebb7-3d34-4ac8-8e1c-40915392cd49', 'level' => 4],
            ['id' => '5d0a77ce-0695-4472-afbb-c049577ed68f', 'region' => 'Республика Калмыкия', 'parent_id' => 'ad871288-3201-4c87-867d-044afdce39b6', 'level' => 4],
            ['id' => '7ca9b814-77fb-474c-896d-f019ef9132fd', 'region' => 'Владимирская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => 'fc8d0161-8948-464f-a37f-1653f13027ec', 'region' => 'Республика Карелия', 'parent_id' => '6bc63d3e-acbb-4728-82d2-4819886c9078', 'level' => 4],
            ['id' => '0733328d-83d1-4d37-b2f7-159b063d214f', 'region' => 'Забайкальский край', 'parent_id' => 'a8511d70-9a5b-4d88-9713-ad0e8c07ec14', 'level' => 4],
            ['id' => '9497a4b5-eef5-4791-a264-a27ab63bf60f', 'region' => 'Астраханская область', 'parent_id' => 'ad871288-3201-4c87-867d-044afdce39b6', 'level' => 4],
            ['id' => '107f336f-f3d8-4ee8-9625-096cc814bdd4', 'region' => 'Вологодская область', 'parent_id' => '6bc63d3e-acbb-4728-82d2-4819886c9078', 'level' => 4],
            ['id' => '330cbc68-8d03-4b1d-92fc-091cdd581009', 'region' => 'Ярославская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => '76f70141-c277-448b-a26a-84a9ab9a0bb0', 'region' => 'Севастополь', 'parent_id' => 'ad871288-3201-4c87-867d-044afdce39b6', 'level' => 4],
            ['id' => '74862cfd-fc2d-4985-b024-703ec531182e', 'region' => 'Татарстан', 'parent_id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'level' => 4],
            ['id' => 'e807636b-6b7d-48dc-bce8-156dd2a0435c', 'region' => 'Тамбовская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => '40f12ce3-1047-42cb-a4f5-8fdac5730eef', 'region' => 'Самарская область', 'parent_id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'level' => 4],
            ['id' => 'df962317-c787-47a3-80f4-74190d27c995', 'region' => 'Мурманская область', 'parent_id' => '6bc63d3e-acbb-4728-82d2-4819886c9078', 'level' => 4],
            ['id' => '85a0a667-64df-4658-ba1b-100e0166a5d3', 'region' => 'Чукотский автономный округ', 'parent_id' => 'a8511d70-9a5b-4d88-9713-ad0e8c07ec14', 'level' => 4],
            ['id' => '1bb9e641-504f-4f73-bf45-583ff0767814', 'region' => 'Приморский край', 'parent_id' => 'a8511d70-9a5b-4d88-9713-ad0e8c07ec14', 'level' => 4],
            ['id' => 'a8b9f46e-7c32-47cd-81e7-5b7bf0aefbdd', 'region' => 'Саратовская область', 'parent_id' => 'c6b22e4c-3c0f-4743-bfd3-a929bfe5d3c5', 'level' => 4],
            ['id' => '96e5e738-c566-4cf0-93f2-7e261d5ec6af', 'region' => 'Новосибирская область', 'parent_id' => 'b8c1ebb7-3d34-4ac8-8e1c-40915392cd49', 'level' => 4],
            ['id' => 'aec8c2c4-cb34-4890-82cd-5e77a591fa3a', 'region' => 'Воронежская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => 'd0382c62-4b3a-464b-8063-414d1a74a39d', 'region' => 'Ростовская область', 'parent_id' => 'ad871288-3201-4c87-867d-044afdce39b6', 'level' => 4],
            ['id' => 'b7b8e017-7918-43de-a2cc-d3b50f5ed2a6', 'region' => 'Калужская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => '62540746-cdd3-4863-9cdb-bc678d99e41b', 'region' => 'Алтайский край', 'parent_id' => 'b8c1ebb7-3d34-4ac8-8e1c-40915392cd49', 'level' => 4],
            ['id' => 'f1e18914-3ce5-40a7-8011-17c57191dcd5', 'region' => 'Севастополь', 'parent_id' => 'ad871288-3201-4c87-867d-044afdce39b6', 'level' => 4],
            ['id' => '65e1ac1a-9e48-47b1-90d4-91dd8c7582f6', 'region' => 'Чечня', 'parent_id' => '7e82c216-c3d3-476d-8538-bde6b933a374', 'level' => 4],
            ['id' => 'be29aae1-e234-4e7d-864f-8751a97a6fcd', 'region' => 'Амурская область', 'parent_id' => 'a8511d70-9a5b-4d88-9713-ad0e8c07ec14', 'level' => 4],
            ['id' => '6d72be02-c3cd-45ee-8238-580b15dd8858', 'region' => 'Ямало-Ненецкий автономный округ', 'parent_id' => 'c0cc6248-839a-4694-ad93-da6426a392b1', 'level' => 4],
            ['id' => '8b5eacf6-6020-4bbd-8a70-6c5c13e7f6ad', 'region' => 'Хабаровский край', 'parent_id' => 'a8511d70-9a5b-4d88-9713-ad0e8c07ec14', 'level' => 4],
            ['id' => 'aceab033-19ba-46b8-bd58-f6f219592cdd', 'region' => 'Московская область', 'parent_id' => '630250b4-92c1-4753-bdff-809e6cf868f0', 'level' => 4],
            ['id' => 'a084ee86-13a0-4448-b4f0-befb899532b1', 'region' => 'Республика Хакасия', 'parent_id' => 'b8c1ebb7-3d34-4ac8-8e1c-40915392cd49', 'level' => 4],
            ['id' => 'c80c5456-7569-4e94-afe2-cd7b6165cce1', 'region' => 'Республика Саха (Якутия)', 'parent_id' => 'a8511d70-9a5b-4d88-9713-ad0e8c07ec14', 'level' => 4],
            ['id' => '5adafd6f-321d-45ac-bbce-caaf0901008e', 'region' => 'Ненецкий автономный округ', 'parent_id' => '6bc63d3e-acbb-4728-82d2-4819886c9078', 'level' => 4],
        ];
    }
}
