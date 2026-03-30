@php
    echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    echo '<?mso-application progid="Excel.Sheet"?>' . PHP_EOL;
@endphp
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
          xmlns:o="urn:schemas-microsoft-com:office:office"
          xmlns:x="urn:schemas-microsoft-com:office:excel"
          xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
          xmlns:html="http://www.w3.org/TR/REC-html40">
    <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
        <Author>Sistema Biblioteca</Author>
        <Created>{{ now()->toAtomString() }}</Created>
        <Company>UNAMAD</Company>
        <Version>16.00</Version>
    </DocumentProperties>
    <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
        <WindowHeight>12000</WindowHeight>
        <WindowWidth>28000</WindowWidth>
        <ProtectStructure>False</ProtectStructure>
        <ProtectWindows>False</ProtectWindows>
    </ExcelWorkbook>
    <Styles>
        <Style ss:ID="Default" ss:Name="Normal">
            <Alignment ss:Vertical="Center"/>
            <Borders/>
            <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#111827"/>
            <Interior/>
            <NumberFormat/>
            <Protection/>
        </Style>
        <Style ss:ID="Title">
            <Font ss:FontName="Calibri" ss:Size="15" ss:Bold="1" ss:Color="#0F172A"/>
            <Alignment ss:Vertical="Center"/>
        </Style>
        <Style ss:ID="Meta">
            <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#334155"/>
            <Alignment ss:Vertical="Center"/>
        </Style>
        <Style ss:ID="Header">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
            </Borders>
            <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#111827"/>
            <Interior ss:Color="#D9D9D9" ss:Pattern="Solid"/>
        </Style>
        <Style ss:ID="Cell">
            <Alignment ss:Vertical="Top" ss:WrapText="1"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
            </Borders>
        </Style>
        <Style ss:ID="CellCenter">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
            </Borders>
        </Style>
        <Style ss:ID="CellNumber">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
            </Borders>
            <NumberFormat ss:Format="0"/>
        </Style>
        <Style ss:ID="CellText">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
            </Borders>
            <NumberFormat ss:Format="@"/>
        </Style>
        <Style ss:ID="TotalLabel">
            <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
            </Borders>
            <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#0F172A"/>
            <Interior ss:Color="#E8EEF7" ss:Pattern="Solid"/>
        </Style>
        <Style ss:ID="TotalValue">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
            </Borders>
            <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#0F172A"/>
            <Interior ss:Color="#E8EEF7" ss:Pattern="Solid"/>
            <NumberFormat ss:Format="0"/>
        </Style>
    </Styles>
    @php($ultimaFila = $registros->count() + 4)
    <Worksheet ss:Name="Inventario fisico">
        <Table ss:ExpandedColumnCount="18" ss:ExpandedRowCount="{{ $ultimaFila + 1 }}" x:FullColumns="1" x:FullRows="1" ss:DefaultRowHeight="18">
            @for ($indice = 1; $indice <= 18; $indice++)
                <Column ss:Index="{{ $indice }}" ss:AutoFitWidth="0" ss:Width="{{ $anchoColumnas[$indice] ?? 90 }}"/>
            @endfor

            <Row ss:Height="24">
                <Cell ss:MergeAcross="17" ss:StyleID="Title">
                    <Data ss:Type="String">Reporte de inventario fisico</Data>
                </Cell>
            </Row>
            <Row ss:Height="18">
                <Cell ss:MergeAcross="17" ss:StyleID="Meta">
                    <Data ss:Type="String">Biblioteca: {{ $biblioteca }}</Data>
                </Cell>
            </Row>
            <Row ss:Height="18">
                <Cell ss:MergeAcross="17" ss:StyleID="Meta">
                    <Data ss:Type="String">Generado: {{ $generadoEn->format('d/m/Y H:i') }}</Data>
                </Cell>
            </Row>
            <Row ss:Height="42">
                <Cell ss:StyleID="Header"><Data ss:Type="String">N°</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">MATERIA</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">MAT.</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">CORR.</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">CÓDIGO</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">CÓDIGO DE PROGRAMA</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">TÍTULO</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">AUTORES</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">AÑOS DE PUBLICACIÓN</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">NÚMERO DE EJEMPLARES</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Idioma</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Edición</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">ISBN</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">País</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">PAGINA</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">EDITORIAL</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">FECHA</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">OBS</Data></Cell>
            </Row>
            @forelse ($registros as $index => $registro)
                <Row ss:AutoFitHeight="1">
                    <Cell ss:StyleID="CellNumber"><Data ss:Type="Number">{{ $index + 1 }}</Data></Cell>
                    <Cell ss:StyleID="Cell"><Data ss:Type="String">{{ $registro->materias ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellText"><Data ss:Type="String">{{ $registro->codigo_dewey ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellNumber"><Data ss:Type="Number">{{ (int) $registro->total_ejemplares }}</Data></Cell>
                    <Cell ss:StyleID="CellText"><Data ss:Type="String">{{ $registro->codigo ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellText"><Data ss:Type="String">{{ $registro->codigo_ant ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="Cell"><Data ss:Type="String">{{ $registro->titulo ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="Cell"><Data ss:Type="String">{{ $registro->autores ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">{{ $registro->anio_edicion ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellNumber"><Data ss:Type="Number">{{ (int) $registro->total_ejemplares }}</Data></Cell>
                    <Cell ss:StyleID="Cell"><Data ss:Type="String">{{ $registro->idioma_nombre ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">{{ $registro->edicion ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellText"><Data ss:Type="String">{{ $registro->isbn ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="Cell"><Data ss:Type="String">{{ $registro->lugar_publicacion ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">{{ $registro->paginas ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="Cell"><Data ss:Type="String">{{ $registro->editorial_nombre ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">{{ $registro->fecha_publicacion ?: ($registro->anio_edicion ?: '-') }}</Data></Cell>
                    <Cell ss:StyleID="Cell"><Data ss:Type="String">{{ $registro->anotaciones ?: '-' }}</Data></Cell>
                </Row>
            @empty
                <Row>
                    <Cell ss:MergeAcross="17" ss:StyleID="CellCenter">
                        <Data ss:Type="String">Sin registros para exportar.</Data>
                    </Cell>
                </Row>
            @endforelse
            <Row ss:Height="22">
                <Cell ss:MergeAcross="8" ss:StyleID="TotalLabel">
                    <Data ss:Type="String">TOTAL DE EJEMPLARES</Data>
                </Cell>
                <Cell ss:StyleID="TotalValue">
                    <Data ss:Type="Number">{{ $totalEjemplares }}</Data>
                </Cell>
                <Cell ss:MergeAcross="7" ss:StyleID="TotalLabel">
                    <Data ss:Type="String">{{ $registros->count() }} titulos listados</Data>
                </Cell>
            </Row>
        </Table>
        <AutoFilter x:Range="R4C1:R{{ $ultimaFila }}C18" xmlns="urn:schemas-microsoft-com:office:excel"/>
        <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
            <FreezePanes/>
            <FrozenNoSplit/>
            <SplitHorizontal>4</SplitHorizontal>
            <TopRowBottomPane>4</TopRowBottomPane>
            <ActivePane>2</ActivePane>
            <ProtectObjects>False</ProtectObjects>
            <ProtectScenarios>False</ProtectScenarios>
        </WorksheetOptions>
    </Worksheet>
</Workbook>
