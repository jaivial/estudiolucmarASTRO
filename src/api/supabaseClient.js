import { createClient } from '@supabase/supabase-js';

const supabaseUrl = process.env.SUPABASE_URL;
const supabaseKey = process.env.SUPABASE_ANON_KEY;
const schema = 'u212050690_estudiolucmar'; // Replace with your schema name

export const supabase = createClient(supabaseUrl, supabaseKey, {
  schema: schema,
});
