import { createClient } from '@supabase/supabase-js';

const supabaseUrl = import.meta.env.PUBLIC_SUPABASE_URL;
const supabaseKey = import.meta.env.PUBLIC_SUPABASE_ANON_KEY;
const schema = import.meta.env.PUBLIC_SUPABASE_SCHEMA;

export const supabase = createClient(supabaseUrl, supabaseKey, { db: { schema: schema } });

console.log('schema', schema); // Debugging line
