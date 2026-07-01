document.addEventListener('DOMContentLoaded', () => {

    // ── Sombra header al hacer scroll ──
    const header = document.querySelector('header');
    window.addEventListener('scroll', () => {
        if (header) header.classList.toggle('active', window.scrollY > 10);
    });

    // ── Cálculo automático nota final ──
    function calcularNota() {
        const ids = ['devocionales', 'cotidianos', 'complementarios', 'proyectos'];
        let suma = 0;
        ids.forEach(id => {
            suma += parseFloat(document.getElementById(id)?.value) || 0;
        });
        const nota = (suma / 4).toFixed(2);
        const el = document.getElementById('nota-resultado');
        if (!el) return;
        el.textContent = nota;
        el.style.color = parseFloat(nota) >= 70 ? 'var(--color-success)' : 'var(--color-danger)';
    }

    ['devocionales', 'cotidianos', 'complementarios', 'proyectos'].forEach(id => {
        const input = document.getElementById(id);
        if (input) input.addEventListener('input', calcularNota);
    });

    // ── Auto-cerrar alertas ──
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => {
            a.style.transition = 'opacity .5s';
            a.style.opacity = '0';
            setTimeout(() => a.remove(), 500);
        });
    }, 3500);
});
