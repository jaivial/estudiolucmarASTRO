// fetchUser.js
import { supabase } from '../supabaseClient.js';

export const fetchUser = async (email, password) => {
  const { data, error } = await supabase.from('users').select('*').eq('email', email).eq('password', password);

  if (error) {
    throw new Error(error.message);
  }

  return data;
};
