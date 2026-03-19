<style>
    .pagination-custom {
        display: flex;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 20px 0;
        flex-wrap: wrap;
        justify-content: flex-start;
    }

    .pagination-custom li {
        margin: 0;
    }

    .pagination-custom li a,
    .pagination-custom li span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 14px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(44, 64, 74, 0.15);
        color: #2c404a;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .pagination-custom li a:hover {
        background: rgba(44, 64, 74, 0.05);
        border-color: #2c404a;
        color: #2c404a;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(44, 64, 74, 0.12);
    }

    .pagination-custom li.active span,
    .pagination-custom li.active a {
        background-color: #2c404a !important;
        color: #ffffff !important;
        border-color: #2c404a !important;
        box-shadow: 0 2px 6px rgba(44, 64, 74, 0.2) !important;
        cursor: default;
    }

    .pagination-custom li.active span:hover,
    .pagination-custom li.active a:hover {
        background-color: #2c404a !important;
        color: #ffffff !important;
    }

    /* Estilo para Anterior/Próximo */
    .pagination-custom li:first-child a,
    .pagination-custom li:last-child a {
        padding: 0 20px;
        font-weight: 600;
        letter-spacing: 0.2px;
    }
</style>