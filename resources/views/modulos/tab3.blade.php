<div id="cy" style="width: 100%; height: 750px; border:1px solid #ccc;"></div>

<script src="https://unpkg.com/cytoscape/dist/cytoscape.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const formularios = @json($grafo['formularios']);
        const relaciones = @json($grafo['relaciones']);

        // Generar nodos
        const nodes = Object.values(formularios).map(form => ({
            data: {
                id: 'form_' + form.id,
                label: `${form.nombre}\n\nCampos:\n${form.campos.map(c => '• ' + c.nombre).join('\n')}`
            }
        }));

        // Generar conexiones
        const edges = relaciones.map(rel => ({
            data: {
                id: `edge_${rel.from}_${rel.to}`,
                source: 'form_' + rel.from,
                target: 'form_' + rel.to,
                label: rel.campo
            }
        }));

        const cy = cytoscape({
            container: document.getElementById('cy'),
            elements: [...nodes, ...edges],
            style: [
                {
                    selector: 'node',
                    style: {
                        'background-color': '#3498db',
                        'label': 'data(label)',
                        'color': '#fff',
                        'text-valign': 'center',
                        'text-halign': 'center',
                        'width': 'label',
                        'height': 'label',
                        'padding': '18px',
                        'shape': 'roundrectangle',
                        'text-wrap': 'wrap',
                        'font-size': '14px',
                        'font-weight': '600'
                    }
                },
                {
                    selector: 'edge',
                    style: {
                        'width': 2,
                        'line-color': '#2c3e50',
                        'target-arrow-color': '#2c3e50',
                        'target-arrow-shape': 'triangle',
                        'curve-style': 'bezier',
                        'label': 'data(label)',
                        'font-size': '13px',
                        'text-background-color': '#fff',
                        'text-background-opacity': 0.8,
                        'text-rotation': 'autorotate'
                    }
                }
            ],
            layout: {
                name: 'breadthfirst',
                directed: true,
                padding: 20,
                spacingFactor: 1.2,
                nodeDimensionsIncludeLabels: true
            }
        });

    });
</script>