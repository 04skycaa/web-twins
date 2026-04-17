-- SQL for creating stock_requests table in Supabase
CREATE TABLE public.stock_requests (
    uuid uuid NOT NULL DEFAULT gen_random_uuid(),
    product_id uuid NULL,
    jumlah_minta integer NOT NULL DEFAULT 1,
    prioritas character varying NOT NULL DEFAULT 'Sedang'::character varying,
    status character varying NOT NULL DEFAULT 'Pending'::character varying,
    pemohon character varying NOT NULL,
    alasan_permintaan text NULL,
    CONSTRAINT stock_requests_pkey PRIMARY KEY (uuid),
    CONSTRAINT stock_requests_product_id_fkey FOREIGN KEY (product_id) REFERENCES products(uuid) ON DELETE SET NULL
) TABLESPACE pg_default;
