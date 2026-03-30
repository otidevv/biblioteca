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
        <WindowWidth>26000</WindowWidth>
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
            <Alignment ss:Vertical="Center" ss:WrapText="1"/>
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
    </Styles>
    @php($ultimaFila = $registros->count() + 4)
    <Worksheet ss:Name="Historial prestamos">
        <Table ss:ExpandedColumnCount="15" ss:ExpandedRowCount="{{ $ultimaFila }}" x:FullColumns="1" x:FullRows="1" ss:DefaultRowHeight="18">
            @for ($indice = 1; $indice <= 15; $indice++)
                <Column ss:Index="{{ $indice }}" ss:AutoFitWidth="0" ss:Width="{{ $anchoColumnas[$indice] ?? 90 }}"/>
            @endfor

            <Row ss:Height="24">
                <Cell ss:MergeAcross="14" ss:StyleID="Title">
                    <Data ss:Type="String">Reporte de historial de prestamos</Data>
                </Cell>
            </Row>
            <Row ss:Height="18">
                <Cell ss:MergeAcross="14" ss:StyleID="Meta">
                    <Data ss:Type="String">Generado: {{ $generadoEn->format('d/m/Y H:i') }}</Data>
                </Cell>
            </Row>
            <Row ss:Height="32">
                <Cell ss:MergeAcross="14" ss:StyleID="Meta">
                    <Data ss:Type="String">Filtros: {{ $filtrosTexto }}</Data>
                </Cell>
            </Row>
            <Row ss:Height="42">
                <Cell ss:StyleID="Header"><Data ss:Type="String">ID</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Libro</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Biblioteca</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Lector</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Ejemplar</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Tipo prestamo</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Estado general</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Estado prestamo</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Fecha prestamo</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Fecha limite</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Fecha devolucion</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Dias prestado</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Registrado por</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Obs prestamo</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">Obs devolucion</Data></Cell>
            </Row>
            @forelse ($registros as $registro)
                <Row ss:AutoFitHeight="1">
                    <Cell ss:StyleID="CellNumber"><Data ss:Type="Number">{{ (int) $registro->id }}</Data></Cell>
                    <Cell ss:StyleID="Cell"><Data ss:Type="String">{{ $registro->titulo ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="Cell"><Data ss:Type="String">{{ $registro->biblioteca_nombre ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="Cell"><Data ss:Type="String">{{ $registro->lector_nombre ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellText"><Data ss:Type="String">{{ $registro->codigo_ejemplar ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">{{ $registro->tipo_prestamo_texto ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">{{ $registro->estado_general_texto ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">{{ $registro->estado_prestamo_texto ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">{{ $registro->fecha_prestamo_texto ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">{{ $registro->fecha_limite_texto ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">{{ $registro->fecha_devolucion_texto ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="CellNumber"><Data ss:Type="Number">{{ (int) ($registro->dias_prestado ?: 0) }}</Data></Cell>
                    <Cell ss:StyleID="Cell"><Data ss:Type="String">{{ $registro->bibliotecario_nombre ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="Cell"><Data ss:Type="String">{{ $registro->observaciones_prestamo ?: '-' }}</Data></Cell>
                    <Cell ss:StyleID="Cell"><Data ss:Type="String">{{ $registro->observaciones_devolucion ?: '-' }}</Data></Cell>
                </Row>
            @empty
                <Row>
                    <Cell ss:MergeAcross="14" ss:StyleID="CellCenter">
                        <Data ss:Type="String">Sin registros para exportar.</Data>
                    </Cell>
                </Row>
            @endforelse
        </Table>
        <AutoFilter x:Range="R4C1:R{{ $ultimaFila }}C15" xmlns="urn:schemas-microsoft-com:office:excel"/>
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
