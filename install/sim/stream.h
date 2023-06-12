/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: stream.h,v 2.10 2020-08-14 16:56:53 dick Exp $
*/

/*
	Interface of the stream module.

	Implements the direct interaction with the lexical
	module.  It supplies the routines below.
*/

extern int Open_Stream(const char *);
extern int Next_Stream_Token_Obtained(void);
extern void Close_Stream(void);
extern void Print_Stream(const char *fname);
