/*	This file is part of the software similarity tester SIM.    -*-mode:C-*-
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: percentages_db.i,v 2.2 2020-08-14 16:56:52 dick Exp $
*/

/* activated by macro DB_DB_PERC */

static void
db_print_match(const struct match *ma) {
	fprintf(Debug_File, "%s < %s, %d/%d=%3.2f%%\n",
		ma->ma_fname0, ma->ma_fname1,
		ma->ma_size, ma->ma_size0,
		match_percentage(ma)*100.0
	);
}

static void
db_print_match_list(const char *msg) {
	fprintf(Debug_File, "\n\n**** DB_PERCENTAGES %s ****\n", msg);
	const struct match *ma;

	for (ma = match_list; ma; ma = ma->ma_next) {
		db_print_match(ma);
	}
	fprintf(Debug_File, "\n");
}
