<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;

use App\Models\User;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Biblioteca;
use App\Models\Carrera;
use App\Models\Persona;
use App\Models\Codido_cutter;
use App\Models\Dewey;

class DatosInicialSeeder extends Seeder
{
    public function run(): void
    {
        
        /** =========================
         *  NOTACION DEL LOS CODIGOS MALAGA O CUTTER
         *  ========================= */
          $parser = new Parser();
        $pdf = $parser->parseFile(database_path('data/notacion.pdf'));

        $texto = $pdf->getText();

        $lineas = explode("\n", $texto);

        foreach ($lineas as $linea) {

            $linea = trim($linea);

            if($linea == '') continue;

            if(preg_match('/^([A-Za-zÁÉÍÓÚÑñ\.\- ]+)\s+(\d+)\s+([A-Za-zÁÉÍÓÚÑñ\.\- ]+)$/u', $linea, $m)){

                $izq = trim($m[1]);
                $codigo = trim($m[2]);
                $der = trim($m[3]);

                DB::table('codido_cutters')->insert([
                    'codigo'=>$codigo,
                    'nombre'=>$izq,
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);

                DB::table('codido_cutters')->insert([
                    'codigo'=>$codigo,
                    'nombre'=>$der,
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);

            }

            elseif(preg_match('/^([A-Za-zÁÉÍÓÚÑñ\.\- ]+)\s+(\d+)$/u', $linea, $m)){

                $nombre = trim($m[1]);
                $codigo = trim($m[2]);

                DB::table('codido_cutters')->insert([
                    'codigo'=>$codigo,
                    'nombre'=>$nombre,
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);

            }

        }
        /** =========================
         *  tIPO DE REGISTRO
         *  ========================= */
         $data = [
            [
                'codigo' => '001',
                'abreviatura' => 'OBRA',
                'nombre' => 'OBRAS-LITERARIAS',
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'codigo' => '002',
                'abreviatura' => 'FABULAS',
                'nombre' => 'FABULAS',
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'codigo' => '003',
                'abreviatura' => 'LIBROS',
                'nombre' => 'LIBROS',
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'codigo' => 'ECO',
                'abreviatura' => 'ECO',
                'nombre' => 'ECOTURISMO',
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'codigo' => 'ISI',
                'abreviatura' => 'ISI',
                'nombre' => 'INGENIERIA DE SISTEMAS',
                'descripcion' => null,
                'estado' => 1,
            ],
        ];
        DB::table('tipo_registros')->insert($data);
        /** =========================
         *  EDITORIALES
         *  ========================= */
        $editoriales = [
            ['nombre'=>'Fao'],
            ['nombre'=>'Heinz Moos Verlag Munich'],
            ['nombre'=>'Saur Editeur'],
            ['nombre'=>'Universidad De Buenos Aires'],
            ['nombre'=>'Addison Wesley'],
            ['nombre'=>'Aique'],
            ['nombre'=>'Alfagrama'],
            ['nombre'=>'Alfaomega'],
            ['nombre'=>'Amorrortu'],
            ['nombre'=>'Arquetipo'],
            ['nombre'=>'Asociacion Casa Editora Sudamerica'],
            ['nombre'=>'Astrea'],
            ['nombre'=>'Biblos'],
            ['nombre'=>'Bonum'],
            ['nombre'=>'Brujas'],
            ['nombre'=>'Cengage'],
            ['nombre'=>'Clacso'],
            ['nombre'=>'Corfo'],
            ['nombre'=>'De Las Ciencias'],
            ['nombre'=>'Debolsillo'],
            ['nombre'=>'Del Puerto'],
            ['nombre'=>'Diseno'],
            ['nombre'=>'Ecoe'],
            ['nombre'=>'Ediciones Novedades Educativas'],
            ['nombre'=>'Edicon'],
            ['nombre'=>'Editores Siglo 21'],
            ['nombre'=>'Editorial Brujas'],
            ['nombre'=>'Editorial 20 Anos'],
            ['nombre'=>'Editorial Cengage'],
            ['nombre'=>'Editorial Corpus Libros'],
            ['nombre'=>'Editorial Granica'],
            ['nombre'=>'Editorial Hemisferio Sur'],
            ['nombre'=>'Editorial Homosapiens'],
            ['nombre'=>'Editorial Ib De F'],
            ['nombre'=>'Editorial Maipue'],
            ['nombre'=>'Editorial Medica Panamericana'],
            ['nombre'=>'Editorial Nueva Libreria'],
            ['nombre'=>'Editorial Urgerman'],
            ['nombre'=>'El Ateneo'],
            ['nombre'=>'Encuentro'],
            ['nombre'=>'Errepar'],
            ['nombre'=>'Espacio'],
            ['nombre'=>'Eudeba'],
            ['nombre'=>'Euros Editores S.R.L'],
            ['nombre'=>'Fondo De Cultura Economica'],
            ['nombre'=>'Fondo De Cultura Economica De Argentina'],
            ['nombre'=>'Fundacion Proturismo'],
            ['nombre'=>'Gran Aldea'],
            ['nombre'=>'Granica'],
            ['nombre'=>'Hemisferio Sur'],
            ['nombre'=>'Historica'],
            ['nombre'=>'Home Sapiens'],
            ['nombre'=>'Homo Sapiens'],
            ['nombre'=>'Homosapiens'],
            ['nombre'=>'Hvmanitas'],
            ['nombre'=>'Hvumanitas'],
            ['nombre'=>'Ifac'],
            ['nombre'=>'Instituto De Investigaciones Gino Germeni'],
            ['nombre'=>'Kapelusz'],
            ['nombre'=>'La Colmena'],
            ['nombre'=>'La Crujia'],
            ['nombre'=>'La Ley'],
            ['nombre'=>'La Rocca'],
            ['nombre'=>'Ladevi'],
            ['nombre'=>'Lexis Nexis'],
            ['nombre'=>'Libreria'],
            ['nombre'=>'Libryco'],
            ['nombre'=>'Losada'],
            ['nombre'=>'Lugar'],
            ['nombre'=>'Lumen'],
            ['nombre'=>'Macchi'],
            ['nombre'=>'Magisterio'],
            ['nombre'=>'Maipue'],
            ['nombre'=>'Manantial'],
            ['nombre'=>'Medica Panamericana'],
            ['nombre'=>'Mundi-Prensa'],
            ['nombre'=>'Nobuko'],
            ['nombre'=>'Novedades Educativa'],
            ['nombre'=>'Novedades Educativas'],
            ['nombre'=>'Noveduc'],
            ['nombre'=>'Omicrom'],
            ['nombre'=>'Omicron System'],
            ['nombre'=>'Osmar D. Buyatti'],
            ['nombre'=>'Oxford'],
            ['nombre'=>'P&J'],
            ['nombre'=>'Paidos'],
            ['nombre'=>'Panamericana'],
            ['nombre'=>'Pearson Educacion'],
            ['nombre'=>'Picanyol'],
            ['nombre'=>'Prentice Hall'],
            ['nombre'=>'Prolam'],
            ['nombre'=>'Puerto Creativo'],
            ['nombre'=>'Reverte'],
            ['nombre'=>'Rubinzall-Culzoni'],
            ['nombre'=>'Saga'],
            ['nombre'=>'Stella'],
            ['nombre'=>'Temas']
        ];

        foreach ($editoriales as &$editorial) {
            $editorial['estado'] = true;
            $editorial['created_at'] = now();
            $editorial['updated_at'] = now();
        }

        DB::table('editoriales')->insert($editoriales);
        /** =========================
         *  PAISES
         *  ========================= */
        $paises = [
            ['nombre' => 'AFGANISTÁN'],
            ['nombre' => 'ALBANIA'],
            ['nombre' => 'ALEMANIA'],
            ['nombre' => 'ANDORRA'],
            ['nombre' => 'ANGOLA'],
            ['nombre' => 'ANGUILA'],
            ['nombre' => 'ANTÁRTIDA'],
            ['nombre' => 'ANTIGUA Y BARBUDA'],
            ['nombre' => 'ARABIA SAUDITA'],
            ['nombre' => 'ARGELIA'],
            ['nombre' => 'ARGENTINA'],
            ['nombre' => 'ARMENIA'],
            ['nombre' => 'ARUBA'],
            ['nombre' => 'AUSTRALIA'],
            ['nombre' => 'AUSTRIA'],
            ['nombre' => 'AZERBAIYÁN'],
            ['nombre' => 'BÉLGICA'],
            ['nombre' => 'BAHAMAS'],
            ['nombre' => 'BAHREIN'],
            ['nombre' => 'BANGLADESH'],
            ['nombre' => 'BARBADOS'],
            ['nombre' => 'BELICE'],
            ['nombre' => 'BENÍN'],
            ['nombre' => 'BHUTÁN'],
            ['nombre' => 'BIELORRUSIA'],
            ['nombre' => 'BIRMANIA'],
            ['nombre' => 'BOLIVIA'],
            ['nombre' => 'BOSNIA Y HERZEGOVINA'],
            ['nombre' => 'BOTSUANA'],
            ['nombre' => 'BRASIL'],
            ['nombre' => 'BRUNÉI'],
            ['nombre' => 'BULGARIA'],
            ['nombre' => 'BURKINA FASO'],
            ['nombre' => 'BURUNDI'],
            ['nombre' => 'CABO VERDE'],
            ['nombre' => 'CAMBOYA'],
            ['nombre' => 'CAMERÚN'],
            ['nombre' => 'CANADÁ'],
            ['nombre' => 'CHAD'],
            ['nombre' => 'CHILE'],
            ['nombre' => 'CHINA'],
            ['nombre' => 'CHIPRE'],
            ['nombre' => 'CIUDAD DEL VATICANO'],
            ['nombre' => 'COLOMBIA'],
            ['nombre' => 'COMORAS'],
            ['nombre' => 'REPÚBLICA DEL CONGO'],
            ['nombre' => 'REPÚBLICA DEMOCRÁTICA DEL CONGO'],
            ['nombre' => 'COREA DEL NORTE'],
            ['nombre' => 'COREA DEL SUR'],
            ['nombre' => 'COSTA DE MARFIL'],
            ['nombre' => 'COSTA RICA'],
            ['nombre' => 'CROACIA'],
            ['nombre' => 'CUBA'],
            ['nombre' => 'CURAZAO'],
            ['nombre' => 'DINAMARCA'],
            ['nombre' => 'DOMINICA'],
            ['nombre' => 'ECUADOR'],
            ['nombre' => 'EGIPTO'],
            ['nombre' => 'EL SALVADOR'],
            ['nombre' => 'EMIRATOS ÁRABES UNIDOS'],
            ['nombre' => 'ERITREA'],
            ['nombre' => 'ESLOVAQUIA'],
            ['nombre' => 'ESLOVENIA'],
            ['nombre' => 'ESPAÑA'],
            ['nombre' => 'ESTADOS UNIDOS DE AMÉRICA'],
            ['nombre' => 'ESTONIA'],
            ['nombre' => 'ETIOPÍA'],
            ['nombre' => 'FILIPINAS'],
            ['nombre' => 'FINLANDIA'],
            ['nombre' => 'FIYI'],
            ['nombre' => 'FRANCIA'],
            ['nombre' => 'GABÓN'],
            ['nombre' => 'GAMBIA'],
            ['nombre' => 'GEORGIA'],
            ['nombre' => 'GHANA'],
            ['nombre' => 'GIBRALTAR'],
            ['nombre' => 'GRANADA'],
            ['nombre' => 'GRECIA'],
            ['nombre' => 'GROENLANDIA'],
            ['nombre' => 'GUADALUPE'],
            ['nombre' => 'GUAM'],
            ['nombre' => 'GUATEMALA'],
            ['nombre' => 'GUAYANA FRANCESA'],
            ['nombre' => 'GUERNSEY'],
            ['nombre' => 'GUINEA'],
            ['nombre' => 'GUINEA ECUATORIAL'],
            ['nombre' => 'GUINEA-BISSAU'],
            ['nombre' => 'GUYANA'],
            ['nombre' => 'HAITÍ'],
            ['nombre' => 'HONDURAS'],
            ['nombre' => 'HONG KONG'],
            ['nombre' => 'HUNGRÍA'],
            ['nombre' => 'INDIA'],
            ['nombre' => 'INDONESIA'],
            ['nombre' => 'IRÁN'],
            ['nombre' => 'IRAK'],
            ['nombre' => 'IRLANDA'],
            ['nombre' => 'ISLANDIA'],
            ['nombre' => 'ISRAEL'],
            ['nombre' => 'ITALIA'],
            ['nombre' => 'JAMAICA'],
            ['nombre' => 'JAPÓN'],
            ['nombre' => 'JORDANIA'],
            ['nombre' => 'KAZAJISTÁN'],
            ['nombre' => 'KENIA'],
            ['nombre' => 'KUWAIT'],
            ['nombre' => 'LAOS'],
            ['nombre' => 'LÍBANO'],
            ['nombre' => 'LESOTO'],
            ['nombre' => 'LETONIA'],
            ['nombre' => 'LIBERIA'],
            ['nombre' => 'LIBIA'],
            ['nombre' => 'LIECHTENSTEIN'],
            ['nombre' => 'LITUANIA'],
            ['nombre' => 'LUXEMBURGO'],
            ['nombre' => 'MÉXICO'],
            ['nombre' => 'MÓNACO'],
            ['nombre' => 'MADAGASCAR'],
            ['nombre' => 'MALASIA'],
            ['nombre' => 'MALAWI'],
            ['nombre' => 'MALI'],
            ['nombre' => 'MALTA'],
            ['nombre' => 'MARRUECOS'],
            ['nombre' => 'MAURICIO'],
            ['nombre' => 'MAURITANIA'],
            ['nombre' => 'MICRONESIA'],
            ['nombre' => 'MOLDAVIA'],
            ['nombre' => 'MONGOLIA'],
            ['nombre' => 'MONTENEGRO'],
            ['nombre' => 'MOZAMBIQUE'],
            ['nombre' => 'NAMIBIA'],
            ['nombre' => 'NEPAL'],
            ['nombre' => 'NICARAGUA'],
            ['nombre' => 'NIGER'],
            ['nombre' => 'NIGERIA'],
            ['nombre' => 'NORUEGA'],
            ['nombre' => 'NUEVA ZELANDA'],
            ['nombre' => 'OMÁN'],
            ['nombre' => 'PAÍSES BAJOS'],
            ['nombre' => 'PAKISTÁN'],
            ['nombre' => 'PANAMÁ'],
            ['nombre' => 'PARAGUAY'],
            ['nombre' => 'PERÚ'],
            ['nombre' => 'POLONIA'],
            ['nombre' => 'PORTUGAL'],
            ['nombre' => 'PUERTO RICO'],
            ['nombre' => 'QATAR'],
            ['nombre' => 'REINO UNIDO'],
            ['nombre' => 'REPÚBLICA CHECA'],
            ['nombre' => 'REPÚBLICA DOMINICANA'],
            ['nombre' => 'RUMANÍA'],
            ['nombre' => 'RUSIA'],
            ['nombre' => 'SENEGAL'],
            ['nombre' => 'SERBIA'],
            ['nombre' => 'SEYCHELLES'],
            ['nombre' => 'SIERRA LEONA'],
            ['nombre' => 'SINGAPUR'],
            ['nombre' => 'SIRIA'],
            ['nombre' => 'SOMALIA'],
            ['nombre' => 'SRI LANKA'],
            ['nombre' => 'SUDÁFRICA'],
            ['nombre' => 'SUDÁN'],
            ['nombre' => 'SUECIA'],
            ['nombre' => 'SUIZA'],
            ['nombre' => 'SURINÁM'],
            ['nombre' => 'TAILANDIA'],
            ['nombre' => 'TANZANIA'],
            ['nombre' => 'TIMOR ORIENTAL'],
            ['nombre' => 'TOGO'],
            ['nombre' => 'TONGA'],
            ['nombre' => 'TRINIDAD Y TOBAGO'],
            ['nombre' => 'TÚNEZ'],
            ['nombre' => 'TURQUÍA'],
            ['nombre' => 'UCRANIA'],
            ['nombre' => 'UGANDA'],
            ['nombre' => 'URUGUAY'],
            ['nombre' => 'UZBEKISTÁN'],
            ['nombre' => 'VANUATU'],
            ['nombre' => 'VENEZUELA'],
            ['nombre' => 'VIETNAM'],
            ['nombre' => 'YEMEN'],
            ['nombre' => 'YIBUTI'],
            ['nombre' => 'ZAMBIA'],
            ['nombre' => 'ZIMBABUE']
        ];

        DB::table('paises')->insert($paises);
        /** =========================
         *  IDIOMAS
         *  ========================= */
        $idiomas = [
            ['nombre' => 'INGLÉS'],
            ['nombre' => 'CHINO MANDARÍN'],
            ['nombre' => 'HINDI'],
            ['nombre' => 'ESPAÑOL'],
            ['nombre' => 'FRANCÉS'],
            ['nombre' => 'ÁRABE'],
            ['nombre' => 'BENGALÍ'],
            ['nombre' => 'PORTUGUÉS'],
            ['nombre' => 'RUSO'],
            ['nombre' => 'URDU'],
            ['nombre' => 'INDONESIO'],
            ['nombre' => 'ALEMÁN'],
            ['nombre' => 'JAPONÉS'],
            ['nombre' => 'MARATÍ'],
            ['nombre' => 'TELUGÚ'],
            ['nombre' => 'TURCO'],
            ['nombre' => 'TAMIL'],
            ['nombre' => 'CHINO CANTONÉS (YUE)'],
            ['nombre' => 'VIETNAMITA'],
            ['nombre' => 'TAGALO'],
            ['nombre' => 'CHINO WU'],
            ['nombre' => 'COREANO'],
            ['nombre' => 'PERSA IRANÍ'],
            ['nombre' => 'HAUSA'],
            ['nombre' => 'ÁRABE EGIPCIO'],
            ['nombre' => 'SUAJILI'],
            ['nombre' => 'JAVANÉS'],
            ['nombre' => 'ITALIANO'],
            ['nombre' => 'PANYABÍ OCCIDENTAL'],
            ['nombre' => 'CANARÉS'],
            ['nombre' => 'GUYARATI'],
            ['nombre' => 'TAILANDÉS'],
            ['nombre' => 'AMÁRICO'],
            ['nombre' => 'BHOSHPURI'],
            ['nombre' => 'PANYABÍ'],
            ['nombre' => 'CHINO MǏN NÁN'],
            ['nombre' => 'CHINO JIN'],
            ['nombre' => 'YORUBA'],
            ['nombre' => 'CHINO HAKKA'],
            ['nombre' => 'BIRMANO'],
            ['nombre' => 'ÁRABE SUDANÉS'],
            ['nombre' => 'POLACO'],
            ['nombre' => 'ÁRABE ARGELINO'],
            ['nombre' => 'LINGALA'],
        ];

        DB::table('idiomas')->insert($idiomas);
        /** =========================
         *  CODIGOS CUTTER
         *  ========================= */
        /*DB::transaction(function(){
            $data = json_decode(
                file_get_contents(database_path('data/codigos.json')),
                true
            );
            foreach ($data as $item) {
                Codido_cutter::create([
                    'codigo' => $item['codigo'],
                    'nombre' => $item['valor'],
                ]);
            }
        });*/
        /** =========================
         *  CODIGOS DEWEY
         *  ========================= */
        DB::transaction(function(){
            $data = json_decode(
                file_get_contents(database_path('data/dewey.json')),
                true
            );
            foreach ($data as $item) {
                $parent_id = null;
                if (!empty($item['dewy_id'])) {
                    $parent = Dewey::where('codigo', $item['dewy_id'])->first();
                    $parent_id = $parent?->id;
                }
                Dewey::create([
                    'codigo' => $item['codigo'],
                    'nombre' => $item['nombre'],
                    'nivel' => $item['nivel'],
                    'dewey_id' => $parent_id
                ]);
            }
        });

        /** =========================
         *  CODIGOS AUTOR
         *  ========================= */
        $autores = [
            ['id'=>171360,'nombre' => 'XAVIER', 'apellido' => 'FRANCH'],
            ['id'=>171361,'nombre' => 'OSVALDO', 'apellido' => 'CAIRO'],
            ['id'=>171362,'nombre' => 'SILVIA', 'apellido' => 'GUARDATI'],
            ['id'=>171363,'nombre' => 'JAMES', 'apellido' => 'JOHNSON'],
            ['id'=>171364,'nombre' => 'CESAR', 'apellido' => 'PEREZ'],
            ['id'=>171365,'nombre' => 'DOUGLAS', 'apellido' => 'LINDNER'],
            ['id'=>171366,'nombre' => 'ENRIQUE', 'apellido' => 'CORDOBA'],
            ['id'=>171367,'nombre' => 'CARMEN', 'apellido' => 'GONZALES'],
            ['id'=>171368,'nombre' => 'CARMEN', 'apellido' => 'CORDOBA'],
            ['id'=>171369,'nombre' => 'JOHN', 'apellido' => 'WAKERLY'],
            ['id'=>171370,'nombre' => 'FRANCISCO', 'apellido' => 'PASCUAL'],
            ['id'=>171371,'nombre' => 'MACRO', 'apellido' => 'EDITORIAL'],
            ['id'=>171372,'nombre' => 'LUIS', 'apellido' => 'RAMIREZ'],
            ['id'=>171373,'nombre' => 'EDUARDO', 'apellido' => 'ROSALES'],
            ['id'=>171374,'nombre' => 'ABRAHAM', 'apellido' => 'SILBERSCHATZ'],
            ['id'=>171375,'nombre' => 'STEVE', 'apellido' => 'SHAH'],
            ['id'=>171376,'nombre' => 'ED', 'apellido' => 'BOTT'],
            ['id'=>171377,'nombre' => 'CARL', 'apellido' => 'SIECHERT'],
            ['id'=>171378,'nombre' => 'JESUS', 'apellido' => 'CARRETERO'],
            ['id'=>171379,'nombre' => 'FELIX', 'apellido' => 'GARCIA'],
            ['id'=>171380,'nombre' => 'PEDRO', 'apellido' => 'DE MIGUEL'],
            ['id'=>171381,'nombre' => 'FERNANDO', 'apellido' => 'PEREZ'],
            ['id'=>171382,'nombre' => 'ROGER', 'apellido' => 'PRESSMAN'],
            ['id'=>171383,'nombre' => 'JUAN', 'apellido' => 'CORDERO'],
            ['id'=>171384,'nombre' => 'JOSE', 'apellido' => 'CORTES'],
            ['id'=>171385,'nombre' => 'LUIS', 'apellido' => 'GARCIA'],
            ['id'=>171386,'nombre' => 'JUAN', 'apellido' => 'CUADRADO'],
            ['id'=>171387,'nombre' => 'ANTONIO', 'apellido' => 'DE AMESCUA'],
            ['id'=>171388,'nombre' => 'MANUEL', 'apellido' => 'VELASCO'],
            ['id'=>171389,'nombre' => 'RICHARD', 'apellido' => 'BIRD'],
            ['id'=>171390,'nombre' => 'ERIC', 'apellido' => 'BRAUDE'],
            ['id'=>171391,'nombre' => 'RAFAEL', 'apellido' => 'FERRE'],
            ['id'=>171392,'nombre' => 'IAN', 'apellido' => 'SOMMERVILLE'],
            ['id'=>171393,'nombre' => 'SILVIA', 'apellido' => 'CAMPOS'],
            ['id'=>171394,'nombre' => 'LUIS', 'apellido' => 'JOYANES'],
            ['id'=>171395,'nombre' => 'ELMER', 'apellido' => 'RAMIREZ'],
            ['id'=>171396,'nombre' => 'JESUS', 'apellido' => 'COSTAS'],
            ['id'=>171397,'nombre' => 'JOHN', 'apellido' => 'SHARP'],
            ['id'=>171398,'nombre' => 'JOSE', 'apellido' => 'CERRADA'],
            ['id'=>171399,'nombre' => 'MANUEL', 'apellido' => 'COLLADO'],
            ['id'=>171400,'nombre' => 'KARL', 'apellido' => 'ASTROM'],
            ['id'=>171401,'nombre' => 'BJORN', 'apellido' => 'WITTENMARK'],
            ['id'=>171402,'nombre' => 'ALAN', 'apellido' => 'COHEN'],
            ['id'=>171403,'nombre' => 'TOMAS', 'apellido' => 'HURTADO'],
            ['id'=>171404,'nombre' => 'MANUEL', 'apellido' => 'CASTRO'],
            ['id'=>171405,'nombre' => 'ANTONIO', 'apellido' => 'COLMENAR'],
            ['id'=>171406,'nombre' => 'PABLO', 'apellido' => 'LOSADA'],
            ['id'=>171407,'nombre' => 'JUAN', 'apellido' => 'PEIRE'],
            ['id'=>171408,'nombre' => 'RAFAEL', 'apellido' => 'ZALVIDEA'],
            ['id'=>171409,'nombre' => 'LON', 'apellido' => 'POOLE'],
        ];


        foreach ($autores as $autor) {
            DB::table('autores')->insert([
                'id' => $autor['id'],
                'nombres' => $autor['nombre'],
                'apellidos' => $autor['apellido'],
                'pais' => null,
            ]);
        }



        /** =========================
         *  MATERIA
         *  ========================= */

        DB::table('materias')->insert([
            [
                'codigo' => '001',
                'abreviatura' => 'CI',
                'nombre' => 'COMPUTACION E INFORMATICA',
                'descripcion' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => '0652955D',
                'abreviatura' => 'CEI',
                'nombre' => 'COMPUTACION E INFORMATICA',
                'descripcion' => 'Materia de COMPUTACION E INFORMATICA',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'D35DCE48',
                'abreviatura' => 'INV',
                'nombre' => 'INVESTIGACION',
                'descripcion' => 'Materia de INVESTIGACION',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => '500',
                'abreviatura' => 'MAT',
                'nombre' => 'MATEMATICA',
                'descripcion' => 'Materia de MATEMATICA',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'A2336E80',
                'abreviatura' => 'QUI',
                'nombre' => 'QUIMICA',
                'descripcion' => 'Materia de QUIMICA',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'B4176A09',
                'abreviatura' => 'FIS',
                'nombre' => 'FISICA',
                'descripcion' => 'Materia de FISICA',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'FB23EF1B',
                'abreviatura' => 'OL',
                'nombre' => 'OBRAS LITERARIAS',
                'descripcion' => 'Materia de OBRAS LITERARIAS',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => '30CFB849',
                'abreviatura' => 'HIS',
                'nombre' => 'HISTORIA',
                'descripcion' => 'Materia de HISTORIA',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'F062EE99',
                'abreviatura' => 'GEO',
                'nombre' => 'GEOGRAFIA',
                'descripcion' => 'Materia de GEOGRAFIA',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'PSI',
                'abreviatura' => 'PSI',
                'nombre' => 'PSICOLOGIA',
                'descripcion' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        /** =========================
         *  BIBLIOTECAS
         *  ========================= */
        $bibliotecas = [
            [
                'codigo' => 'CENTRAL',
                'nombre' => 'BIBLIOTECA CENTRAL',
                'direccion' => 'CIUDAD UNIVERSITARIA',
                'descripcion' => null,
                'estado' => 'activo',
            ],
            [
                'codigo' => 'BEIS',
                'nombre' => 'BIBLIOTECA ESPECIALIZADA DE ING DE SISTEMAS',
                'direccion' => null,
                'descripcion' => null,
                'estado' => 'activo',
            ],
            [
                'codigo' => 'BEE',
                'nombre' => 'BIBLIOTECA ESPECIALIZADA DE ENFERMERIA',
                'direccion' => null,
                'descripcion' => null,
                'estado' => 'activo',
            ],
        ];

        foreach ($bibliotecas as $data) {
            Biblioteca::updateOrCreate(
                ['codigo' => $data['codigo']],
                $data
            );
        }

        /** =========================
         *  ROL ADMIN
         *  ========================= */
        $rolAdmin = Rol::firstOrCreate(
            ['nombre' => 'ADMIN'],
            ['descripcion' => 'Administrador del sistema']
        );
        Rol::firstOrCreate(
            ['nombre' => 'LECTOR'],
            ['descripcion' => 'Lector del sistema']
        );
        /** =========================
         *  CARRERA
         *  ========================= */
        $carrera = Carrera::firstOrCreate(
            ['codigo' => 'ADM'],
            [
                'nombre' => 'Administración del Sistema',
                'facultad' => 'Sistemas',
                'descripcion' => 'Carrera administrativa del sistema',
                'activo' => true,
            ]
        );
        $carrera = Carrera::firstOrCreate(
            ['codigo' => 'ADM'],
            [
                'nombre' => 'ADMINISTRACION',
                'facultad' => 'FACULTAD DE CIENCIAS EMPRESARIALES',
                'descripcion' => 'CARRERA PROFESIONAL DE ADMINISTRACION',
                'activo' => true,
            ]
        );
        $carrera = Carrera::firstOrCreate(
            ['codigo' => 'CON'],
            [
                'nombre' => 'CONTABILIDAD Y FINANZAS',
                'facultad' => 'FACULTAD DE CIENCIAS EMPRESARIALES',
                'descripcion' => 'CARRERA PROFESIONAL DE CONTABILIDAD Y FINANZAS',
                'activo' => true,
            ]
        );
        $carrera = Carrera::firstOrCreate(
            ['codigo' => 'ECO'],
            [
                'nombre' => 'ECOTURISMO',
                'facultad' => 'FACULTAD DE ECOTURISMO',
                'descripcion' => 'CARRERA PROFESIONAL DE ECOTURISMO',
                'activo' => true,
            ]
        );
        $carrera = Carrera::firstOrCreate(
            ['codigo' => 'FOR'],
            [
                'nombre' => 'INGENIERIA FORESTAL Y MEDIO AMBIENTE',
                'facultad' => 'FACULTAD DE INGENIERIA FORESTAL Y MEDIO AMBIENTE',
                'descripcion' => 'CARRERA PROFESIONAL DE INGENIERIA FORESTAL Y MEDIO AMBIENTE',
                'activo' => true,
            ]
        );
        $carrera = Carrera::firstOrCreate(
            ['codigo' => 'SIS'],
            [
                'nombre' => 'INGENIERIA DE SISTEMAS E INFORMATICA',
                'facultad' => 'FACULTAD DE INGENIERIA',
                'descripcion' => 'CARRERA PROFESIONAL DE INGENIERIA DE SISTEMAS E INFORMATICA',
                'activo' => true,
            ]
        );
        $carrera = Carrera::firstOrCreate(
            ['codigo' => 'AGRO'],
            [
                'nombre' => 'INGENIERIA AGROINDUSTRIAL',
                'facultad' => 'FACULTAD DE INGENIERIA',
                'descripcion' => 'CARRERA PROFESIONAL DE INGENIERIA AGROINDUSTRIAL',
                'activo' => true,
            ]
        );
        $carrera = Carrera::firstOrCreate(
            ['codigo' => 'MEDVET'],
            [
                'nombre' => 'MEDICINA VETERINARIA Y ZOOTECNIA',
                'facultad' => 'FACULTAD DE MEDICINA VETERINARIA Y ZOOTECNIA',
                'descripcion' => 'CARRERA PROFESIONAL DE MEDICINA VETERINARIA Y ZOOTECNIA',
                'activo' => true,
            ]
        );
        $carrera = Carrera::firstOrCreate(
            ['codigo' => 'DER'],
            [
                'nombre' => 'DERECHO Y CIENCIAS POLITICAS',
                'facultad' => 'FACULTAD DE DERECHO Y CIENCIAS POLITICAS',
                'descripcion' => 'CARRERA PROFESIONAL DE DERECHO Y CIENCIAS POLITICAS',
                'activo' => true,
            ]
        );
        $carrera = Carrera::firstOrCreate(
            ['codigo' => 'ENF'],
            [
                'nombre' => 'ENFERMERIA',
                'facultad' => 'FACULTAD DE CIENCIAS DE LA SALUD',
                'descripcion' => 'CARRERA PROFESIONAL DE ENFERMERIA',
                'activo' => true,
            ]
        );

        /** =========================
         *  PERSONA ADMIN
         *  ========================= */
        $personaAdmin = Persona::firstOrCreate(
            ['dni' => '00000000'],
            [
                'codigo_institucional' => 'ADMIN001',
                'nombres' => 'Administrador',
                'apellido_paterno' => 'Sistema',
                'apellido_materno' => 'Sistema',
                'sexo' => 'O',
                'email_personal' => 'admin@biblioteca.local',
                'tipo_persona' => 'ADMINISTRATIVO',
                'carrera_id' => $carrera->id,
                'activo' => true,
            ]
        );

        /** =========================
         *  USUARIO ADMIN
         *  ========================= */
        $usuarioAdmin = User::firstOrCreate(
            ['email' => 'admin@biblioteca.local'],
            [
                'uuid' => Str::uuid(),
                'name' => 'admin',
                'password' => Hash::make('12345678'),
                'tipo_usuario' => 'ADMIN',
                'estado' => 'activo',
                'origen' => 'local',
                'persona_id' => $personaAdmin->id,
            ]
        );

        /** =========================
         *  ASIGNACIÓN ROL (GLOBAL)
         *  biblioteca_id = NULL
         *  ========================= */
        DB::table('usuario_rol_bibliotecas')->updateOrInsert(
            [
                'user_id' => $usuarioAdmin->id,
                'rol_id' => $rolAdmin->id,
                'biblioteca_id' => null, // 👈 acceso a TODAS
            ],
            [
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        /** =========================
         *  PERMISOS
         *  ========================= */

        // ADMINISTRACIÓN
        $permisoAdmin = Permiso::firstOrCreate(
            ['codigo' => 'administracion'],
            ['nombre' => 'Administración']
        );

        $this->crearPermisos([
            ['administracion.usuarios', 'Gestión de Usuarios'],
            ['administracion.roles_permisos', 'Roles y Permisos'],
            ['administracion.bibliotecas', 'Gestión de Bibliotecas'],
            ['administracion.backups', 'Backups'],
            ['administracion.proveedores', 'Gestión de proveedores'],
            ['administracion.editoriales', 'Gestión de editoriales'],
            ['administracion.tipo_registros', 'Gestión de tipo_registros'],
            ['administracion.autores', 'Gestión de autores'],
        ], $permisoAdmin->id);

        // LECTORES
        $lectores = Permiso::firstOrCreate(
            ['codigo' => 'lectores'],
            ['nombre' => 'Lectores']
        );

        $this->crearPermisos([
            ['lectores.registro', 'Registro de Lectores'],
            ['lectores.historial', 'Historial de Préstamos'],
            ['lectores.penalizaciones', 'Penalizaciones y Multas'],
            ['lectores.importacion', 'Importación de Usuarios'],
        ], $lectores->id);

        // CATÁLOGO
        Permiso::firstOrCreate(['codigo' => 'catalogo'], ['nombre' => 'Catálogo']);

        // PRÉSTAMOS
        $prestamos = Permiso::firstOrCreate(
            ['codigo' => 'prestamos'],
            ['nombre' => 'Préstamos y Gestión']
        );

        $this->crearPermisos([
            ['prestamos.registro', 'Préstamos y Devoluciones'],
            ['prestamos.reservas', 'Reservas'],
            ['prestamos.multas', 'Multas y Sanciones'],
        ], $prestamos->id);

        // REPORTES
        Permiso::firstOrCreate(['codigo' => 'reportes'], ['nombre' => 'Reportes']);

        // INVENTARIO
        $inventario = Permiso::firstOrCreate(
            ['codigo' => 'inventario'],
            ['nombre' => 'Inventario y Extras']
        );

        $this->crearPermisos([
            ['inventario.fisico', 'Inventario Físico'],
            ['inventario.digital', 'Material Digital'],
            ['inventario.notificaciones', 'Notificaciones'],
            ['inventario.compras', 'Gestión de Compras'],
            ['inventario.libros', 'Gestión de Libros'],
        ], $inventario->id);

        /** =========================
         *  ASIGNAR TODOS LOS PERMISOS AL ADMIN
         *  ========================= */
        $permisos = Permiso::pluck('id');

        foreach ($permisos as $permisoId) {
            DB::table('rol_permisos')->updateOrInsert(
                [
                    'rol_id' => $rolAdmin->id,
                    'permiso_id' => $permisoId,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        
    }

    private function crearPermisos(array $permisos, int $permisoPadreId): void
    {
        foreach ($permisos as [$codigo, $nombre]) {
            Permiso::firstOrCreate(
                ['codigo' => $codigo],
                [
                    'nombre' => $nombre,
                    'descripcion' => $nombre,
                    'permiso_id' => $permisoPadreId,
                ]
            );
        }
    }
}